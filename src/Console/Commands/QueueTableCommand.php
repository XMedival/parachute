<?php

namespace Parachute\Console\Commands;

class QueueTableCommand implements Command
{
    public function handle(array $args, string $basePath): int
    {
        $migrationsPath = $basePath . '/database/migrations';
        $timestamp = date('Y_m_d_His');

        if (!is_dir($migrationsPath)) {
            mkdir($migrationsPath, 0755, true);
        }

        // Create jobs table migration
        $jobsStub = file_get_contents(__DIR__ . '/../../Queue/stubs/create_jobs_table.stub');
        $jobsMigrationPath = $migrationsPath . "/{$timestamp}_create_jobs_table.php";

        if (!file_exists($jobsMigrationPath)) {
            file_put_contents($jobsMigrationPath, $jobsStub);
            echo "\033[32m✓ Migration created: {$timestamp}_create_jobs_table.php\033[0m\n";
        } else {
            echo "\033[33m! Jobs migration already exists\033[0m\n";
        }

        // Wait a second for unique timestamp
        sleep(1);
        $timestamp = date('Y_m_d_His');

        // Create failed_jobs table migration
        $failedJobsStub = file_get_contents(__DIR__ . '/../../Queue/stubs/create_failed_jobs_table.stub');
        $failedJobsMigrationPath = $migrationsPath . "/{$timestamp}_create_failed_jobs_table.php";

        if (!file_exists($failedJobsMigrationPath)) {
            file_put_contents($failedJobsMigrationPath, $failedJobsStub);
            echo "\033[32m✓ Migration created: {$timestamp}_create_failed_jobs_table.php\033[0m\n";
        } else {
            echo "\033[33m! Failed jobs migration already exists\033[0m\n";
        }

        echo "\n\033[36mRun './radio migrate' to create the tables.\033[0m\n";

        return 0;
    }
}
