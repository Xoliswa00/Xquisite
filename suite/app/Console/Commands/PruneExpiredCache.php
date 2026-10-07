<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The database cache store only deletes an expired row when that same key is
 * read again. Keys that are never read twice (one per failed sign-in email,
 * per rate-limit window, per one-off marker) stay in the table for good.
 */
class PruneExpiredCache extends Command
{
    protected $signature = 'cache:prune-expired {--chunk=5000 : Rows to delete per pass}';

    protected $description = 'Delete expired rows from the database cache and cache-lock tables';

    public function handle(): int
    {
        $store  = config('cache.default');
        $config = config("cache.stores.{$store}", []);

        if (($config['driver'] ?? null) !== 'database') {
            $this->info("Cache store [{$store}] is not the database store; nothing to prune.");

            return self::SUCCESS;
        }

        $connection = DB::connection($config['connection'] ?? null);
        $table      = $config['table'] ?? 'cache';
        $chunk      = max(1, (int) $this->option('chunk'));

        foreach (array_unique([$table, $config['lock_table'] ?? $table . '_locks']) as $name) {
            if (! Schema::connection($connection->getName())->hasTable($name)) {
                continue;
            }

            $deleted = 0;

            // Small passes so a large backlog never holds one long lock on a
            // table every request reads.
            do {
                $pass = $connection->table($name)->where('expiration', '<', time())->limit($chunk)->delete();
                $deleted += $pass;
            } while ($pass === $chunk);

            $this->info("{$name}: removed {$deleted} expired row(s).");
        }

        return self::SUCCESS;
    }
}
