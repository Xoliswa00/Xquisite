<?php

namespace App\Jobs;

use App\Models\MonitoredInstance;
use App\Models\InstanceAlert;
use App\Models\HealthCheckLog;
use App\Notifications\InstanceDownNotification;
use App\Notifications\InstanceUpNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class CheckInstanceHealth implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 30;
    public $tries = 3;
    public $backoff = [10, 30, 60];

    public function __construct(
        public MonitoredInstance $instance
    ) {}

    public function handle(): void
    {
        if (!$this->instance->is_active) {
            return;
        }

        try {
            $startTime = microtime(true);
            
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->instance->api_token,
                ])
                ->get($this->instance->url . '/api/health');

            $responseTime = (int) ((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $this->handleHealthSuccess($response->json(), $responseTime);
            } else {
                $this->handleHealthFailure("HTTP " . $response->status(), $responseTime);
            }
        } catch (\Exception $e) {
            $this->handleHealthFailure($e->getMessage(), null);
        }
    }

    private function notifyAdmin(object $notification): void
    {
        $email = config('app.admin_email', config('mail.from.address'));
        if ($email) {
            Notification::route('mail', $email)->notify($notification);
        }
    }

    private function handleHealthSuccess(array $data, int $responseTime): void
    {
        $isUp = ($data['status'] ?? 'down') === 'up';

        HealthCheckLog::create([
            'monitored_instance_id' => $this->instance->id,
            'status' => $isUp ? 'up' : 'down',
            'response_time_ms' => $responseTime,
            'error_message' => null,
            'metadata' => $data,
            'checked_at' => now(),
        ]);

        if ($isUp) {
            $wasDown = $this->instance->status === 'down';

            $this->instance->update([
                'status' => 'up',
                'last_check_at' => now(),
                'consecutive_failures' => 0,
            ]);

            // Resolve any active down alerts
            if ($wasDown) {
                InstanceAlert::where('monitored_instance_id', $this->instance->id)
                    ->where('type', 'down')
                    ->where('is_resolved', false)
                    ->each(fn(InstanceAlert $alert) => $alert->resolve());

                InstanceAlert::create([
                    'monitored_instance_id' => $this->instance->id,
                    'type' => 'up',
                    'title' => $this->instance->name . ' is back online',
                    'message' => 'Instance recovered at ' . now()->format('Y-m-d H:i:s'),
                    'is_resolved' => false,
                ]);

                $this->notifyAdmin(new InstanceUpNotification($this->instance));
            }
        } else {
            $this->handleDownInstance();
        }
    }

    private function handleHealthFailure(string $error, ?int $responseTime): void
    {
        $this->instance->increment('consecutive_failures');

        HealthCheckLog::create([
            'monitored_instance_id' => $this->instance->id,
            'status' => 'down',
            'response_time_ms' => $responseTime,
            'error_message' => $error,
            'metadata' => null,
            'checked_at' => now(),
        ]);

        if ($this->instance->consecutive_failures >= 3) {
            $this->handleDownInstance();
        }
    }

    private function handleDownInstance(): void
    {
        $wasUp = $this->instance->status === 'up';

        $this->instance->update([
            'status' => 'down',
            'last_check_at' => now(),
            'last_error_at' => now(),
        ]);

        $alreadyAlerted = InstanceAlert::where('monitored_instance_id', $this->instance->id)
            ->where('type', 'down')
            ->where('is_resolved', false)
            ->exists();

        if ($wasUp || !$alreadyAlerted) {
            InstanceAlert::create([
                'monitored_instance_id' => $this->instance->id,
                'type' => 'down',
                'title' => $this->instance->name . ' is down',
                'message' => 'Instance failed health checks at ' . now()->format('Y-m-d H:i:s'),
                'is_resolved' => false,
            ]);

            Log::warning('Instance down', [
                'instance_id'   => $this->instance->id,
                'instance_name' => $this->instance->name,
            ]);

            $lastError = $this->instance->last_error_message;
            $this->notifyAdmin(new InstanceDownNotification($this->instance, $lastError));
        }
    }
}
