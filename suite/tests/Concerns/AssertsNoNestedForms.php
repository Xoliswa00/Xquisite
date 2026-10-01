<?php

namespace Tests\Concerns;

/**
 * DOMDocument (libxml2's HTML parser) does NOT replicate real browsers'
 * HTML5 tree-construction quirk for a <form> nested inside another
 * <form> — real browsers drop the inner <form> START tag but still
 * process its END tag against the still-open outer form, silently
 * closing it early (the exact bug fixed in both
 * products/variants.blade.php and admin/logs/index.blade.php).
 * libxml2 instead just keeps the nested <form> as a real child node,
 * so a DOMDocument-based test (getElementById/XPath lookups) PASSES
 * against the original, genuinely broken markup — confirmed empirically
 * by temporarily reverting the admin/logs fix and re-running such a
 * test, which kept passing. DOMDocument-based structural assertions are
 * still fine for "did I remove/change a field", just not sufficient
 * proof that nothing is nested.
 *
 * This is the actual authoritative check: scan the raw Blade SOURCE
 * (not the rendered/parsed HTML) for literal <form ...> / </form>
 * tokens in order, with Blade comments stripped first (the exact
 * mechanism, applied to a real token this time rather than prose, that
 * Blade itself uses to strip {{-- ... --}} before compiling) tracking
 * nesting depth. A genuine <form><form>...</form></form> in the source
 * is the real bug regardless of which @if branch executes it, and this
 * needs no routes, auth, or a booted app to run.
 */
trait AssertsNoNestedForms
{
    protected function assertNoNestedFormsInSource(string $relativeViewPath): void
    {
        $path = base_path('resources/views/' . $relativeViewPath);
        $this->assertFileExists($path, "Blade view not found: {$relativeViewPath}");

        $source = file_get_contents($path);
        $source = preg_replace('/\{\{--.*?--\}\}/s', '', $source);

        preg_match_all('/<form\b[^>]*>|<\/form>/', $source, $matches);

        $depth = 0;
        foreach ($matches[0] as $token) {
            if (str_starts_with($token, '<form')) {
                $depth++;
                $this->assertLessThanOrEqual(
                    1,
                    $depth,
                    "Found a <form> nested inside another <form> in {$relativeViewPath} — invalid HTML. ".
                    "Real browsers drop the inner <form> start tag but still honour its end tag against ".
                    "the still-open outer form, silently closing it early (confirmed: DOMDocument-based ".
                    "tests do NOT catch this, libxml2 doesn't replicate the quirk — this source-level ".
                    "check is the real guard)."
                );
            } else {
                $depth = max(0, $depth - 1);
            }
        }
    }
}
