<?php

namespace Parachute\Console\Commands;

use Parachute\Support\Facades\DB;
use Parachute\Support\Facades\Schema;
use Parachute\Database\Blueprint;

class MigrateFreshCommand implements Command
{
    public function handle(array $args, string $basePath): int
    {
        echo "Running database migrations...\n";

        $migrationsPath = $basePath . '/database/migrations';
        $migrationFiles = glob($migrationsPath . '/*.php');

        $tables = DB::getTables();
        foreach ($tables as $table) {
            echo "Dropping table: " . $table . "\n";
            Schema::dropIfExists($table);
        }

        $this->createMigrationsTable();
        foreach ($migrationFiles as $file) {
            $migrationName = basename($file, '.php');
            $migration = require_once $file;
            if (method_exists($migration, 'up')) {
                echo "Migrating: " . basename($file) . "\n";
                $migration->up();
                DB::table('migrations')->insert([
                    'migration' => "$migrationName",
                    'batch' => 1,
                ]);
            } else {
                echo "\033[31m✗ Invalid migration file: " . basename($file) . "\033[0m\n";
            }
        }

        echo "Migrations completed.\n";
        return 0;
    }

    protected function getNextBatchNumber(): int
    {
        $latestMigration = DB::table('migrations')->orderBy('batch', 'desc')->first();
        return ($lastMigration['batch'] ?? 0) + 1;
    }

    protected function createMigrationsTable(): void
    {
        Schema::create('migrations', function ($table) {
            $table->id();
            $table->string('migration');
            $table->integer('batch');
        });
    }
}
