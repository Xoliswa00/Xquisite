<?php

namespace App\Support;

use Illuminate\Mail\Markdown;

/**
 * Mail renderer whose injection protection cannot be switched off by the
 * view cache.
 *
 * Markdown::withSecuredEncoding() works by compiling the mail templates with
 * a special echo format. Blade caches compiled templates by file path only,
 * so if a template was compiled first by anything else (`php artisan
 * view:cache` or `optimize` at deploy, a command that pre-compiles views) the
 * plain compiled copy is reused and the protection is silently skipped: a
 * name like `[Approve](https://evil.example)` becomes a live link again.
 *
 * This renderer compiles mail templates into their own folder for the length
 * of each HTML render, so they are always compiled under the secured format
 * and never collide with the normal view cache. It covers every notification
 * and Markdown mailable in the app, present and future.
 *
 * Bound in AppServiceProvider. Proven by
 * tests/Feature/Security/InjectionRegressionTest.php, which pre-compiles the
 * templates the plain way first.
 */
class SecureMarkdown extends Markdown
{
    public function render($view, array $data = [], $inliner = null)
    {
        $compiler = $this->view->getEngineResolver()->resolve('blade')->getCompiler();

        // The compiler has no public setter for its cache path.
        $swap = function (string $path): string {
            $previous = $this->cachePath;
            $this->cachePath = $path;

            return $previous;
        };

        $original = $swap->call($compiler, storage_path('framework/views-mail'));

        try {
            return parent::render($view, $data, $inliner);
        } finally {
            $swap->call($compiler, $original);
        }
    }
}
