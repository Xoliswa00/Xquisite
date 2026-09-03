<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Customer;
use App\Modules\Booking\Observers\CustomerObserver;
use App\Observers\AppointmentObserver;
use App\Observers\UserObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Appointment::observe(AppointmentObserver::class);
        Customer::observe(CustomerObserver::class);
        User::observe(UserObserver::class);

        $this->registerRateLimiters();
        $this->registerSlowQueryDetector();
        $this->registerFailedJobAlerts();
    }

    private function registerRateLimiters(): void
    {
        // Login / auth — 10 attempts per minute per IP
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip())->response(function () {
                abort(429, 'Too many login attempts. Please wait before trying again.');
            });
        });

        // General API / web routes — 120 per minute per authenticated user or IP
        RateLimiter::for('global', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(120)->by($request->user()->id)
                : Limit::perMinute(60)->by($request->ip());
        });

        // Admin area — stricter limit
        RateLimiter::for('admin', function (Request $request) {
            return Limit::perMinute(80)->by($request->user()?->id ?? $request->ip());
        });

        // Cross-project log ingest (/ingest/logs) — keyed by the reporter's
        // bearer token, not IP, so several reporters behind one egress IP (or a
        // burst draining a backlog after hub downtime) don't starve each other.
        RateLimiter::for('ingest', function (Request $request) {
            return Limit::perMinute(120)->by($request->bearerToken() ?: $request->ip());
        });
    }

    private function registerSlowQueryDetector(): void
    {
        if (app()->isProduction()) {
            DB::listen(function ($query) {
                $thresholdMs = 1000;

                if ($query->time < $thresholdMs) {
                    return;
                }

                try {
                    DB::table('system_logs')->insert([
                        'level'      => 'WARNING',
                        'message'    => '[SlowQuery] ' . round($query->time) . 'ms — ' . mb_substr($query->sql, 0, 500),
                        'context'    => json_encode([
                            'sql'      => $query->sql,
                            'bindings' => $query->bindings,
                            'time_ms'  => $query->time,
                        ]),
                        'url'        => request()->fullUrl(),
                        'ip_address' => request()->ip(),
                        'user_id'    => auth()->id(),
                        'source'     => 'suite',
                        'status'     => 'new',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } catch (\Throwable) {
                    // Never let logging kill a request
                }
            });
        }
    }

    /**
     * A queued job (any job, any queue) exhausting its retries is always worth
     * knowing about — go through Log::critical() rather than a direct system_logs
     * insert so it also fires the existing CriticalLogAlert email, the same way
     * property:health-check findings do.
     */
    private function registerFailedJobAlerts(): void
    {
        Queue::failing(function (JobFailed $event) {
            Log::critical('[queue] Job failed permanently: ' . $event->job->resolveName(), [
                'connection' => $event->connectionName,
                'queue'      => $event->job->getQueue(),
                'job_id'     => $event->job->getJobId(),
                'attempts'   => $event->job->attempts(),
                'exception'  => $event->exception->getMessage(),
            ]);
        });
    }
}
