<?php

namespace Parachute\Queue;

class Worker
{
    protected Queue $queue;
    protected bool $shouldQuit = false;
    protected int $sleep;
    protected int $timeout;
    protected int $memoryLimit;

    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * Start the queue worker daemon.
     */
    public function daemon(string $queueName, array $options = []): void
    {
        $this->sleep = $options['sleep'] ?? 3;
        $this->timeout = $options['timeout'] ?? 60;
        $this->memoryLimit = $options['memory'] ?? 128;

        $this->registerSignalHandlers();

        while (!$this->shouldQuit) {
            $job = $this->queue->pop($queueName);

            if ($job) {
                $this->process($job);
            } else {
                $this->sleep($this->sleep);
            }

            if ($this->memoryExceeded()) {
                $this->stop();
            }
        }
    }

    /**
     * Process a job from the queue.
     */
    public function process(array $jobRecord): void
    {
        try {
            $job = unserialize($jobRecord['payload']);

            if (!$job instanceof Job) {
                throw new \RuntimeException('Invalid job payload');
            }

            echo "[" . date('Y-m-d H:i:s') . "] Processing: " . get_class($job) . "\n";

            // Execute the job
            $job->handle();

            // Job succeeded, remove from queue
            $this->queue->delete($jobRecord['id']);

            echo "[" . date('Y-m-d H:i:s') . "] Processed: " . get_class($job) . "\n";

        } catch (\Throwable $e) {
            $this->handleJobException($jobRecord, $e);
        }
    }

    /**
     * Handle an exception that occurred while processing a job.
     */
    protected function handleJobException(array $jobRecord, \Throwable $e): void
    {
        $job = @unserialize($jobRecord['payload']);
        $maxTries = $job instanceof Job ? $job->getTries() : 3;
        $retryAfter = $job instanceof Job ? $job->getRetryAfter() : 60;

        echo "[" . date('Y-m-d H:i:s') . "] Failed: " . ($job ? get_class($job) : 'Unknown') . " - " . $e->getMessage() . "\n";

        if ($jobRecord['attempts'] >= $maxTries) {
            // Max attempts reached, mark as failed
            $this->queue->fail($jobRecord, $e);

            // Call the failed method on the job
            if ($job instanceof Job) {
                try {
                    $job->failed($e);
                } catch (\Throwable $failedException) {
                    // Ignore exceptions in failed handler
                }
            }

            echo "[" . date('Y-m-d H:i:s') . "] Job moved to failed jobs table\n";
        } else {
            // Release back to queue for retry
            $this->queue->release($jobRecord['id'], $retryAfter);
            echo "[" . date('Y-m-d H:i:s') . "] Job will be retried in {$retryAfter} seconds\n";
        }
    }

    /**
     * Register signal handlers for graceful shutdown.
     */
    protected function registerSignalHandlers(): void
    {
        if (!extension_loaded('pcntl')) {
            return;
        }

        pcntl_async_signals(true);

        pcntl_signal(SIGTERM, function () {
            $this->shouldQuit = true;
        });

        pcntl_signal(SIGINT, function () {
            $this->shouldQuit = true;
        });
    }

    /**
     * Sleep the worker for a given number of seconds.
     */
    protected function sleep(int $seconds): void
    {
        sleep($seconds);
    }

    /**
     * Check if the memory limit has been exceeded.
     */
    protected function memoryExceeded(): bool
    {
        return (memory_get_usage(true) / 1024 / 1024) >= $this->memoryLimit;
    }

    /**
     * Stop the worker.
     */
    public function stop(): void
    {
        $this->shouldQuit = true;
    }
}
