<?php

namespace Parachute\Console\Commands;

class MakeJobCommand implements Command
{
    public function handle(array $args, string $basePath): int
    {
        if (empty($args)) {
            echo "\033[31m✗ Please provide a job name.\033[0m\n";
            echo "Usage: \033[33m./radio make:job <JobName>\033[0m\n";
            return 1;
        }

        $jobName = ucfirst($args[0]);
        $jobPath = $basePath . "/app/Jobs/{$jobName}.php";

        if (file_exists($jobPath)) {
            echo "\033[31m✗ Job already exists: {$jobName}\033[0m\n";
            return 1;
        }

        $jobTemplate = "<?php

namespace App\Jobs;

use Parachute\Queue\Job;

class {$jobName} extends Job
{
    /**
     * The number of times the job may be attempted.
     */
    protected int \$tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    protected int \$retryAfter = 60;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable \$e): void
    {
        //
    }
}
";

        $dir = dirname($jobPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        echo "\033[36mCreating job: {$jobName}...\033[0m\n";
        file_put_contents($jobPath, $jobTemplate);
        echo "\033[32m✓ Job created: app/Jobs/{$jobName}.php\033[0m\n";

        return 0;
    }
}
