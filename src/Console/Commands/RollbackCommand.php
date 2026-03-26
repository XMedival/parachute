<?php

namespace Parachute\Console\Commands;

use Parachute\Support\Facades\DB;

class RollbackCommand implements Command
{
    public function handle(array $args, string $basePath): int
    {
        echo "Rolling back database migrations...\n";

        $migrationsPath = $basePath . '/database/migrations';
        $migrationFiles = glob($migrationsPath . '/*.php');

        $migrations = DB::table('migrations')->orderBy('batch', 'desc')->get();
        $lastBatch = $this->getLastBatch($basePath);

        foreach ($migrations as $migration) {
            if ($migration['batch'] === $lastBatch) {
                $migrationFile = $migrationsPath . '/' . $migration['migration'] . '.php';

                if (file_exists($migrationFile)) {
                    $migrationClass = require_once $migrationFile;
                    if (method_exists($migrationClass, 'down')) {
                        echo "Rolling back: {$migration['migration']}\n";
                        $migrationClass->down();
                        DB::table('migrations')->where('id', $migration['id'])->delete();
                    } else {
                        echo "No down method found for: {$migration['migration']}\n";
                    }
                } else {
                    echo "Migration file not found: {$migrationFile}\n";
                }
            }
        }

        echo "Migrations completed successfully.\n";
        return 0;
    }

    protected function getLastBatch(string $basePath): int
    {
        return DB::table('migrations')->orderBy('batch', 'desc')->first()['batch'] ?? 1;
    }
}
