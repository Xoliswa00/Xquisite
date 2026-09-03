<?php

namespace App\Console\Commands;

use App\Models\MonitoredInstance;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Registers (or repairs) a reporter's MonitoredInstance row from the CLI so a
 * new project can be pointed at this hub without hand-filling the
 * /admin/monitoring form and inventing a 32-char token.
 *
 * Idempotent: run it again with the same slug and it only fills gaps
 * (missing slug on a legacy row, is_active flipped false, a changed url).
 * The api_token is preserved across re-runs unless --rotate-token is passed.
 *
 * Prints the four env lines the reporter needs. See docs/MONITORING_INGEST.md.
 */
class RegisterMonitoredInstance extends Command
{
    protected $signature = 'monitoring:register-instance
        {slug : Stable lowercase identifier, e.g. "nobela" (alpha-dash)}
        {--name= : Human label (defaults to a title-cased slug)}
        {--url= : The reporter\'s own GET /api/health endpoint, for the pull check}
        {--rotate-token : Issue a fresh api_token even if the instance already exists}
        {--deactivate : Set is_active=false for this slug instead of registering}';

    protected $description = 'Create or repair a reporter MonitoredInstance and print its credentials';

    public function handle(): int
    {
        $slug = strtolower(trim((string) $this->argument('slug')));

        if (! preg_match('/^[a-z0-9]+(?:[-_][a-z0-9]+)*$/', $slug)) {
            $this->error("Invalid slug '{$slug}' — use lowercase letters, digits, '-' or '_' only.");

            return self::FAILURE;
        }

        $instance = MonitoredInstance::where('slug', $slug)->first();

        // A row created before the slug column existed won't be found above.
        // Adopt it by exact name match (requires --name) so its historical
        // logs keep their source instead of a duplicate row appearing.
        if (! $instance && $this->option('name')) {
            $instance = MonitoredInstance::whereNull('slug')
                ->where('name', $this->option('name'))
                ->first();
        }

        if ($this->option('deactivate')) {
            if (! $instance) {
                $this->error("No monitored instance with slug '{$slug}'.");

                return self::FAILURE;
            }

            $instance->update(['is_active' => false]);
            $this->warn("Instance '{$slug}' deactivated — every monitoring endpoint now 401s for its token.");

            return self::SUCCESS;
        }

        $name = $this->option('name') ?: ($instance->name ?? Str::of($slug)->replace(['-', '_'], ' ')->title()->value());
        $url  = $this->option('url') ?: ($instance->url ?? null);

        if (! $url) {
            $this->error('An instance needs a health-check URL. Pass --url=https://<reporter>/api/health');

            return self::FAILURE;
        }

        $rotating = $this->option('rotate-token') || ! $instance;
        $token    = $rotating ? Str::random(48) : $instance->api_token;

        if ($instance) {
            $instance->update([
                'name'      => $name,
                'slug'      => $slug,
                'url'       => $url,
                'api_token' => $token,
                'is_active' => true,
            ]);
            $this->info("Updated existing instance #{$instance->id} ('{$slug}').");
        } else {
            $instance = MonitoredInstance::create([
                'name'      => $name,
                'slug'      => $slug,
                'url'       => $url,
                'api_token' => $token,
                'status'    => 'unknown',
                'is_active' => true,
            ]);
            $this->info("Created instance #{$instance->id} ('{$slug}').");
        }

        $this->newLine();
        $this->table(['Field', 'Value'], [
            ['name', $instance->name],
            ['slug', $instance->slug],
            ['url', $instance->url],
            ['is_active', $instance->is_active ? 'true' : 'false'],
        ]);

        $this->newLine();
        $this->line('Reporter .env — set these on the reporting project:');
        $this->line('');
        $this->line('  MONITORING_ENABLED=true');
        $this->line('  MONITORING_URL='.rtrim(config('app.url'), '/'));
        $this->line('  MONITORING_TOKEN='.$token);
        $this->line('  MONITORING_SLUG='.$instance->slug);
        $this->newLine();

        if (! $rotating) {
            $this->comment('(api_token unchanged — pass --rotate-token to issue a new one)');
        }

        return self::SUCCESS;
    }
}
