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
use App\Observers\AppointmentObserver;
use App\Observers\UserObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Values echoed into Markdown emails (every notification ->line(), ->greeting())
        // have [ and < escaped, so a name like "[Approve](https://evil.example)" typed
        // into a public form can't become a live link or tracking image in an email
        // we send. The app's own **bold** in those lines still works.
        \Illuminate\Mail\Markdown::withSecuredEncoding();

        Appointment::observe(AppointmentObserver::class);
        // Customer::observe() is intentionally NOT called here — CustomerObserver
        // lives in App\Modules\Booking\Observers and is already picked up by
        // BookingServiceProvider's ObserverRegistrar auto-scan. Registering it a
        // second time here made every created()/updated() hook fire twice per
        // event (harmless for Customer's idempotent Client-sync logic, but it's
        // the same double-registration bug that silently duplicated appointment
        // reminders — see the deleted Modules/Booking/Observers/AppointmentObserver).
        User::observe(UserObserver::class);

        $this->registerRateLimiters();
        $this->registerSlowQueryDetector();
        $this->registerFailedJobAlerts();
    }

    private function registerRateLimiters(): void
    {
        // Login / auth: 30 posts per minute per IP. One address is often a whole
        // shop or a mobile carrier, so this is only a flood guard; the per-account
        // limits live in LoginThrottleService.
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip())->response(function () {
                abort(429, 'Too many login attempts. Please wait before trying again.');
            });
        });

        // New accounts: generous enough for a shop where several customers sign
        // up on the same Wi-Fi, tight enough to stop scripted sign-ups.
        RateLimiter::for('register', function (Request $request) {
            $refuse = fn () => abort(429, 'Too many sign-ups from this Wi-Fi or network. Please wait a few minutes and try again.');

            return [
                Limit::perMinute(10)->by('register:minute:' . $request->ip())->response($refuse),
                Limit::perHour(60)->by('register:hour:' . $request->ip())->response($refuse),
            ];
        });

        // "Confirm your password" is a password guess against a signed-in account,
        // so it is limited per account, not per network.
        RateLimiter::for('confirm-password', function (Request $request) {
            return Limit::perMinute(5)->by('confirm:' . ($request->user()?->getAuthIdentifier() ?? $request->ip()))->response(function () {
                abort(429, 'Too many wrong passwords. Please wait a minute and try again.');
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

        // Private files (/files/{kind}/{id}) — its own bucket so a photo-heavy
        // inspection page (40+ images) behind one office IP doesn't hit 429s and
        // show broken images. The signature already stops enumeration.
        RateLimiter::for('private-files', function (Request $request) {
            return Limit::perMinute(600)->by('private-files|' . $request->ip());
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
