<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Finder\Finder;

class AuditRouteReferences extends Command
{
    protected $signature   = 'xq:audit-routes {--path=resources/views : Path to scan}';
    protected $description = 'Scan Blade views for route() calls and flag any that do not exist';

    public function handle(): int
    {
        $registeredRoutes = collect(Route::getRoutes()->getRoutesByName())->keys()->all();
        $viewPath         = base_path($this->option('path'));

        $finder = (new Finder)->files()->in($viewPath)->name('*.blade.php');

        $broken  = [];
        $checked = 0;

        foreach ($finder as $file) {
            $contents    = $file->getContents();
            $relPath     = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getRealPath());

            // Match route('name') helper — exclude ->route() method calls (e.g. $request->route('param'))
            preg_match_all("/(?<!->)route\(\s*['\"]([^'\"]+)['\"]/", $contents, $matches, PREG_OFFSET_CAPTURE);

            foreach ($matches[1] as [$routeName, $offset]) {
                $checked++;
                if (!in_array($routeName, $registeredRoutes)) {
                    $line = substr_count(substr($contents, 0, $offset), "\n") + 1;
                    $broken[] = ['file' => $relPath, 'line' => $line, 'route' => $routeName];
                }
            }
        }

        if (empty($broken)) {
            $this->info("✓ All {$checked} route references are valid.");
            return self::SUCCESS;
        }

        $this->newLine();
        $this->error(" {$checked} references checked — " . count($broken) . ' broken:');
        $this->newLine();

        $this->table(['File', 'Line', 'Route name'], array_map(
            fn($b) => [$b['file'], $b['line'], $b['route']],
            $broken
        ));

        $this->newLine();
        $this->warn('Fix the above before deploying.');

        return self::FAILURE;
    }
}
