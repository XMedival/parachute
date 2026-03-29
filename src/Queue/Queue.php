<?php

namespace Parachute\Queue;

use Parachute\Support\Facades\DB;

class Queue
{
    protected array $config;
    protected string $table;
    protected string $failedTable;
    protected string $defaultQueue;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->table = $config['table'] ?? 'jobs';
        $this->failedTable = $config['failed_table'] ?? 'failed_jobs';
        $this->defaultQueue = $config['default'] ?? 'default';
    }

    /**
     * Push a job onto the queue.
     */
    public function push(Job $job, ?string $queue = null): int
    {
        return $this->pushToDatabase($job, $queue ?? $this->defaultQueue, 0);
    }

    /**
     * Push a job onto the queue with a delay.
     */
    public function later(int $delay, Job $job, ?string $queue = null): int
    {
        return $this->pushToDatabase($job, $queue ?? $this->defaultQueue, $delay);
    }

    /**
     * Push the job to the database.
     */
    protected function pushToDatabase(Job $job, string $queue, int $delay): int
    {
        $now = time();
        $availableAt = $now + $delay;

        return DB::table($this->table)->insertGetId([
            'queue' => $queue,
            'payload' => serialize($job),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => $availableAt,
            'created_at' => $now,
        ]);
    }

    /**
     * Pop the next available job from the queue.
     */
    public function pop(?string $queue = null): ?array
    {
        $queue = $queue ?? $this->defaultQueue;
        $now = time();

        $job = DB::table($this->table)
            ->where('queue', $queue)
            ->where('available_at', '<=', $now)
            ->whereNull('reserved_at')
            ->orderBy('id', 'asc')
            ->first();

        if (!$job) {
            return null;
        }

        // Reserve the job
        DB::table($this->table)
            ->where('id', $job['id'])
            ->update([
                'reserved_at' => $now,
                'attempts' => $job['attempts'] + 1,
            ]);

        $job['attempts'] = $job['attempts'] + 1;
        $job['reserved_at'] = $now;

        return $job;
    }

    /**
     * Delete a job from the queue.
     */
    public function delete(int $jobId): bool
    {
        return DB::table($this->table)->where('id', $jobId)->delete() > 0;
    }

    /**
     * Release a job back onto the queue.
     */
    public function release(int $jobId, int $delay = 0): bool
    {
        $availableAt = time() + $delay;

        return DB::table($this->table)
            ->where('id', $jobId)
            ->update([
                'reserved_at' => null,
                'available_at' => $availableAt,
            ]) > 0;
    }

    /**
     * Move a job to the failed jobs table.
     */
    public function fail(array $job, \Throwable $e): int
    {
        // Remove from jobs table
        $this->delete($job['id']);

        // Add to failed jobs table
        return DB::table($this->failedTable)->insertGetId([
            'queue' => $job['queue'],
            'payload' => $job['payload'],
            'exception' => $this->formatException($e),
            'failed_at' => time(),
        ]);
    }

    /**
     * Retry a failed job.
     */
    public function retry(int $failedJobId): bool
    {
        $failedJob = DB::table($this->failedTable)
            ->where('id', $failedJobId)
            ->first();

        if (!$failedJob) {
            return false;
        }

        // Push back to the jobs table
        DB::table($this->table)->insert([
            'queue' => $failedJob['queue'],
            'payload' => $failedJob['payload'],
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => time(),
            'created_at' => time(),
        ]);

        // Remove from failed jobs
        DB::table($this->failedTable)->where('id', $failedJobId)->delete();

        return true;
    }

    /**
     * Retry all failed jobs.
     */
    public function retryAll(): int
    {
        $failedJobs = DB::table($this->failedTable)->get();
        $count = 0;

        foreach ($failedJobs as $failedJob) {
            if ($this->retry($failedJob['id'])) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Format the exception for storage.
     */
    protected function formatException(\Throwable $e): string
    {
        return sprintf(
            "%s: %s in %s:%d\n\nStack trace:\n%s",
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
    }

    /**
     * Get the table name for jobs.
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Get the table name for failed jobs.
     */
    public function getFailedTable(): string
    {
        return $this->failedTable;
    }

    /**
     * Get the default queue name.
     */
    public function getDefaultQueue(): string
    {
        return $this->defaultQueue;
    }
}
