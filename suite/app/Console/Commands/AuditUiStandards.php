<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;

/**
 * Enforces the UI/UX standards accumulated over this project's sessions (see
 * memory: project_ui_ux_standards.md for the full rationale behind each rule).
 * Split into HARD FAILs (zero legitimate exceptions found so far, safe to
 * block on) and WARNINGs (need human judgment — printed, never block).
 *
 * Run via xq:audit-ui, wired into .git/hooks/pre-commit and pre-push.
 */
class AuditUiStandards extends Command
{
    protected $signature   = 'xq:audit-ui {--path=resources/views : Path to scan}';
    protected $description = 'Scan Blade views for known UI/UX anti-patterns (AI-slop, no-op hover, unresponsive tables, duplicate banners)';

    /** Files whose whole purpose IS to define these patterns — never flag them against themselves. */
    private const SELF_REFERENCE = [
        'layouts/app.blade.php',
    ];

    /** A raw <table> is fine if the file also contains one of these three established responsive techniques. */
    private const RESPONSIVE_TABLE_MARKERS = [
        'sm:hidden', 'hidden sm:block', 'md:hidden', 'hidden md:block', 'summary-on-mobile', 'overflow-x-auto',
    ];

    public function handle(): int
    {
        $viewPath = base_path($this->option('path'));
        $finder   = (new Finder)->files()->in($viewPath)->name('*.blade.php');

        $hardFails = [];
        $warnings  = [];
        $checked   = 0;

        foreach ($finder as $file) {
            $contents = $file->getContents();
            $relPath  = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getRealPath());
            $relPath  = str_replace('\\', '/', $relPath);
            $isSelfRef = in_array(str_replace(str_replace('\\', '/', $this->option('path')) . '/', '', $relPath), self::SELF_REFERENCE, true);
            $checked++;

            // ── HARD FAIL: no-op hover — the FULL bg token (hex + any opacity suffix) is
            // identically repeated after hover:, with nothing else following it. A bare
            // hex match isn't enough: bg-[#0078D4]/80 hover:bg-[#0078D4] is a real, visible
            // opacity change, not a no-op — only flag when the two tokens are byte-identical.
            if (preg_match_all('/(bg-\[#[0-9A-Fa-f]{3,6}(?:\/\d{1,3})?\])(?![\/\w])[^"\'\s]*\s+hover:\1(?![\/\w])/', $contents, $m, PREG_OFFSET_CAPTURE)) {
                foreach ($m[0] as [$match, $offset]) {
                    $hardFails[] = ['file' => $relPath, 'line' => $this->lineOf($contents, $offset), 'rule' => 'no-op hover color', 'detail' => trim($match)];
                }
            }

            // ── HARD FAIL: brand typo "Xquisite Creation" missing the "s" ──
            if (preg_match_all('/Xquisite Creation\b(?!s)/', $contents, $m, PREG_OFFSET_CAPTURE)) {
                foreach ($m[0] as [$match, $offset]) {
                    $hardFails[] = ['file' => $relPath, 'line' => $this->lineOf($contents, $offset), 'rule' => 'brand typo', 'detail' => '"Xquisite Creation" missing the "s"'];
                }
            }

            // ── HARD FAIL: gradient headline text (background-clip: text + a multi-stop gradient) ──
            if (preg_match('/background-clip:\s*text/i', $contents) && preg_match('/linear-gradient\(/i', $contents)) {
                $hardFails[] = ['file' => $relPath, 'line' => '—', 'rule' => 'gradient headline text', 'detail' => 'background-clip:text + linear-gradient — see feedback_ai_slop_checklist #4'];
            }

            // ── HARD FAIL: shimmer button sweep animation ──
            if (preg_match('/@keyframes\s+shimmer/i', $contents)) {
                $hardFails[] = ['file' => $relPath, 'line' => '—', 'rule' => 'shimmer button animation', 'detail' => '@keyframes shimmer — see feedback_ai_slop_checklist #6'];
            }

            // ── HARD FAIL: raw <table> with none of the three established responsive techniques ──
            // Email templates and PDF exports (dompdf, printed documents — always named
            // *-pdf.blade.php in this app) use <table> for fixed print/email layout, not an
            // interactive data grid — not subject to a "does it work on a phone" rule at all.
            $isPrintOrEmail = str_contains($relPath, '/emails/') || str_ends_with($relPath, '-pdf.blade.php');
            if (!$isPrintOrEmail && preg_match('/<table\b/i', $contents)) {
                $hasMarker = false;
                foreach (self::RESPONSIVE_TABLE_MARKERS as $marker) {
                    if (str_contains($contents, $marker)) { $hasMarker = true; break; }
                }
                if (!$hasMarker) {
                    $hardFails[] = ['file' => $relPath, 'line' => '—', 'rule' => 'unresponsive table', 'detail' => '<table> with no mobile-card block, summary-on-mobile, or overflow-x-auto'];
                }
            }

            // ── HARD FAIL: page using <x-app-layout> that also renders its own flash banner ──
            if (!$isSelfRef && str_contains($contents, '<x-app-layout>')) {
                foreach (['success', 'error', 'warning', 'info'] as $key) {
                    if (preg_match("/session\(\s*['\"]" . $key . "['\"]\s*\)/", $contents, $m, PREG_OFFSET_CAPTURE)) {
                        $hardFails[] = ['file' => $relPath, 'line' => $this->lineOf($contents, $m[0][1]), 'rule' => 'duplicate flash banner', 'detail' => "session('{$key}') — layouts/app.blade.php already renders this globally"];
                    }
                }
            }

            // ── WARNING: eyebrow-ish pill (rounded-full + uppercase + tracking-wide together) ──
            if (preg_match('/rounded-full/', $contents) && preg_match('/uppercase/', $contents) && preg_match('/tracking-wide/', $contents)) {
                $warnings[] = ['file' => $relPath, 'rule' => 'possible eyebrow', 'detail' => 'rounded-full + uppercase + tracking-wide found together — confirm it is not a kicker/eyebrow label above a heading'];
            }

            // ── WARNING: em-dash clause-join candidate ──
            if (preg_match_all('/\s—\s/u', $contents, $m, PREG_OFFSET_CAPTURE)) {
                foreach ($m[0] as [$match, $offset]) {
                    $warnings[] = ['file' => $relPath, 'line' => $this->lineOf($contents, $offset), 'rule' => 'em-dash usage', 'detail' => 'confirm this is a title separator / brand tagline, not a "clause — clause" tic'];
                }
            }
        }

        $this->printResults($checked, $hardFails, $warnings);

        return empty($hardFails) ? self::SUCCESS : self::FAILURE;
    }

    private function lineOf(string $contents, int $offset): int
    {
        return substr_count(substr($contents, 0, $offset), "\n") + 1;
    }

    private function printResults(int $checked, array $hardFails, array $warnings): void
    {
        if (!empty($warnings)) {
            $this->newLine();
            $this->comment(count($warnings) . ' warning(s) — review, does not block:');
            $this->table(['File', 'Line', 'Rule', 'Detail'], array_map(
                fn ($w) => [$w['file'], $w['line'] ?? '—', $w['rule'], $w['detail']],
                array_slice($warnings, 0, 30)
            ));
            if (count($warnings) > 30) {
                $this->comment('... and ' . (count($warnings) - 30) . ' more warning(s), truncated.');
            }
        }

        if (empty($hardFails)) {
            $this->newLine();
            $this->info("✓ {$checked} views checked — no UI standard violations.");
            return;
        }

        $this->newLine();
        $this->error("{$checked} views checked — " . count($hardFails) . ' violation(s):');
        $this->newLine();

        $this->table(['File', 'Line', 'Rule', 'Detail'], array_map(
            fn ($f) => [$f['file'], $f['line'], $f['rule'], $f['detail']],
            $hardFails
        ));

        $this->newLine();
        $this->warn('Fix the above before committing/pushing. See memory: project_ui_ux_standards.md');
    }
}
