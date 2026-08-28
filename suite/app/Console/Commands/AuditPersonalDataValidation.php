<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;

/**
 * Enforces the standing rule (Xoliswa, 2026-08-28): any form field that
 * captures an ID number, phone/cell number, or email — in any module, any
 * portal — must use real format validation, not just `string|max:X`. A
 * duplicate/garbage value that slips past a bare string rule is what caused
 * the production crash this check exists to prevent (converting an applicant
 * to a renter with a malformed/duplicate email threw a raw DB exception).
 *
 * Scans app/Http/Controllers for `$request->validate([...])` / `->validate([...])`
 * blocks and flags any `id_number`/`phone`/`cell`-shaped field whose rule
 * value doesn't reference the shared App\Rules\SouthAfricanIdNumber /
 * SouthAfricanPhoneNumber classes, and any `email`-shaped field whose rule
 * value doesn't include Laravel's `email` rule. Run via xq:audit-data-integrity,
 * wired into .git/hooks/pre-commit and pre-push so this can't silently regress
 * as new modules/forms get added.
 *
 * Line-based, not a full AST parse — matches the same pragmatic approach as
 * AuditModelsHaveAuditable. Only matches rule-literal values (starting with
 * `'` or `[`), so it never flags a data-assignment line like
 * `'phone' => $request->phone` inside a create()/update() call.
 */
class AuditPersonalDataValidation extends Command
{
    protected $signature   = 'xq:audit-data-integrity {--path=app/Http : Path to scan (Controllers AND Requests — validation rules live in either)}';
    protected $description = 'Scan controller validation rules and flag ID/phone/email fields missing real format validation';

    /**
     * Confirmed-fine exceptions, keyed 'relative/path.php:field'. Treated as
     * closed like AuditModelsHaveAuditable's NO_AUDIT — confirm with Xoliswa
     * before adding, don't just add one to make a red check go green.
     */
    private const EXCLUDED = [
        //
    ];

    private const PHONE_FIELD_PATTERN = '/^(.*_)?(phone|cell|cell_number|mobile)$/i';
    private const ID_FIELD_PATTERN    = '/^(.*_)?id_number$/i';
    private const EMAIL_FIELD_PATTERN = '/^(.*_)?email$/i';

    public function handle(): int
    {
        $basePath = base_path($this->option('path'));
        $finder   = (new Finder)->files()->in($basePath)->name('*.php');

        $violations = [];
        $checked    = 0;

        foreach ($finder as $file) {
            $relPath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getRealPath());
            $lines   = explode("\n", $file->getContents());

            $lineCount = count($lines);

            for ($i = 0; $i < $lineCount; $i++) {
                if (!preg_match("/^\s*'([\w.*]+)'\s*=>\s*(.+?),?\s*$/", $lines[$i], $m)) {
                    continue;
                }

                [, $field, $value] = $m;
                $value = trim($value);

                // Only a rule literal (string or array), never a data-assignment
                // value like `$request->phone` or `$validated['phone']`.
                if (!preg_match('/^([\'"]|\[)/', $value)) {
                    continue;
                }

                // Rule array spans multiple lines (`'field' => [` with the actual
                // rule tokens on following lines) — keep reading forward until
                // brackets balance, so a multi-line style doesn't false-positive.
                $depth = substr_count($value, '[') - substr_count($value, ']');
                $j     = $i;
                while ($depth > 0 && $j + 1 < $lineCount) {
                    $j++;
                    $value .= ' ' . trim($lines[$j]);
                    $depth += substr_count($lines[$j], '[') - substr_count($lines[$j], ']');
                }

                $checked++;
                $key = "{$relPath}:{$field}";

                if (in_array($key, self::EXCLUDED, true)) {
                    continue;
                }

                if (preg_match(self::ID_FIELD_PATTERN, $field) && !str_contains($value, 'SouthAfricanIdNumber')) {
                    $violations[] = ['file' => $relPath, 'line' => $i + 1, 'field' => $field, 'need' => 'App\\Rules\\SouthAfricanIdNumber'];
                } elseif (preg_match(self::PHONE_FIELD_PATTERN, $field) && !str_contains($value, 'SouthAfricanPhoneNumber')) {
                    $violations[] = ['file' => $relPath, 'line' => $i + 1, 'field' => $field, 'need' => 'App\\Rules\\SouthAfricanPhoneNumber'];
                } elseif (preg_match(self::EMAIL_FIELD_PATTERN, $field) && !preg_match('/\bemail\b/', $value)) {
                    $violations[] = ['file' => $relPath, 'line' => $i + 1, 'field' => $field, 'need' => "the 'email' rule"];
                }
            }
        }

        if (empty($violations)) {
            $this->info("✓ All {$checked} ID/phone/email validation rules checked — no data-integrity gaps.");
            return self::SUCCESS;
        }

        $this->newLine();
        $this->error(count($violations) . ' field(s) with weak personal-data validation:');
        $this->newLine();

        $this->table(['File', 'Line', 'Field', 'Needs'], array_map(
            fn ($v) => [$v['file'], $v['line'], $v['field'], $v['need']],
            $violations
        ));

        $this->newLine();
        $this->warn('Use [\'nullable\', new SouthAfricanIdNumber] / [\'nullable\', new SouthAfricanPhoneNumber]');
        $this->warn('(see app/Rules/) instead of a bare string|max rule. If a field genuinely isn\'t a real');
        $this->warn('SA ID/phone/email, add \'path.php:field\' to EXCLUDED in AuditPersonalDataValidation.php —');
        $this->warn('confirm with Xoliswa first, the exclusion list is treated as closed.');

        return self::FAILURE;
    }
}
