<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Customer;
use App\Modules\Booking\Observers\CustomerObserver;
use App\Observers\AppointmentObserver;
use App\Services\Tenant\TenantContext;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Appointment::observe(AppointmentObserver::class);
        Customer::observe(CustomerObserver::class);

        $this->registerRateLimiters();
        $this->registerSlowQueryDetector();
        $this->registerTenantContextQueueGuards();
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
    }

    /**
     * TenantContext is a static, in-process singleton set only by HTTP-layer
     * code (ResolveTenant middleware). Nothing clears it between jobs in a
     * long-running queue worker, so a stale value could otherwise leak from
     * one job into the next. Clearing is skipped for the 'sync' connection,
     * since sync jobs run inline in the dispatching request/process — e.g.
     * UserManagementController::store() queues a welcome email under the
     * test suite's QUEUE_CONNECTION=sync — and must inherit that request's
     * ambient context rather than have it wiped mid-request.
     */
    private function registerTenantContextQueueGuards(): void
    {
        Queue::before(function (JobProcessing $event) {
            if ($event->connectionName !== 'sync') {
                TenantContext::clear();
            }
        });

        Queue::after(function (JobProcessed $event) {
            if ($event->connectionName !== 'sync') {
                TenantContext::clear();
            }
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
}
