<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;

/**
 * Enforces the standing rule (confirmed by Xoliswa, 2026-08-25 / 2026-08-27):
 * every Eloquent model must `use Auditable;` unless it's on the explicit,
 * confirmed exclusion list below. Run via xq:audit-auditable, wired into
 * .git/hooks/pre-commit and pre-push so this can't silently regress.
 */
class AuditModelsHaveAuditable extends Command
{
    protected $signature   = 'xq:audit-auditable {--path=app : Path to scan}';
    protected $description = 'Scan Eloquent models and flag any missing the Auditable trait';

    /**
     * Pure system telemetry — not user-driven records. Auditing them would
     * be circular (AuditLog, SystemLog) or pure noise (health checks,
     * monitoring heartbeats, background queues). Confirmed with Xoliswa;
     * treat as closed — ask before adding to this list.
     */
    private const NO_AUDIT = [
        'AuditLog',
        'SystemLog',
        'HealthCheckLog',
        'InstanceAlert',
        'MonitoredInstance',
        'SyncQueue',
        'BillingQueue',
    ];

    /**
     * Audited through a different mechanism than the trait — still fully
     * covered, just not via `use Auditable;`.
     */
    private const ALTERNATE_MECHANISM = [
        'User' => 'audited via UserObserver (registered in AppServiceProvider), not the trait',
    ];

    public function handle(): int
    {
        $basePath = base_path($this->option('path'));
        $finder   = (new Finder)->files()->in($basePath)->name('*.php');

        $missing = [];
        $checked = 0;
        $skipped = [];

        foreach ($finder as $file) {
            $contents = $file->getContents();

            // Only real Eloquent models — classes directly extending Model or
            // Authenticatable (covers Illuminate\Foundation\Auth\User and similar).
            if (!preg_match('/class\s+(\w+)\s+extends\s+(Model|Authenticatable)\b/', $contents, $m)) {
                continue;
            }

            $className = $m[1];
            $relPath   = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getRealPath());

            if (in_array($className, self::NO_AUDIT, true)) {
                $skipped[] = "{$className} — deliberately excluded (system telemetry)";
                continue;
            }

            if (isset(self::ALTERNATE_MECHANISM[$className])) {
                $skipped[] = "{$className} — " . self::ALTERNATE_MECHANISM[$className];
                continue;
            }

            $checked++;

            // Matches `use Auditable;` / `use HasTenant, Auditable;` etc. inside the class body —
            // distinct from the `use App\Models\Traits\Auditable;` import at the top of the file.
            if (!preg_match('/^\s*use\s+[\w,\s]*\bAuditable\b[\w,\s]*;/m', $contents)) {
                $missing[] = ['file' => $relPath, 'class' => $className];
            }
        }

        if (empty($missing)) {
            $this->info("✓ All {$checked} audited-eligible models use the Auditable trait ({$this->countLabel(count($skipped))} deliberately excluded).");
            return self::SUCCESS;
        }

        $this->newLine();
        $this->error("{$checked} models checked — " . count($missing) . ' missing the Auditable trait:');
        $this->newLine();

        $this->table(['File', 'Class'], array_map(
            fn ($m) => [$m['file'], $m['class']],
            $missing
        ));

        $this->newLine();
        $this->warn('Add `use Auditable;` (App\Models\Traits\Auditable), or if this is genuinely system');
        $this->warn('telemetry with no audit value, add it to NO_AUDIT in AuditModelsHaveAuditable.php —');
        $this->warn('but confirm with Xoliswa first, the exclusion list is treated as closed.');

        return self::FAILURE;
    }

    private function countLabel(int $n): string
    {
        return "{$n} " . ($n === 1 ? 'model' : 'models');
    }
}
