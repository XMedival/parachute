<?php

namespace Parachute\Queue;

use Parachute\Base\App;
use Parachute\Contracts\Job\ShouldQueue;

abstract class Job implements ShouldQueue
{
    /**
     * The number of times the job may be attempted.
     */
    protected int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    protected int $retryAfter = 60;

    /**
     * The queue on which the job should be placed.
     */
    protected ?string $queue = null;

    /**
     * Execute the job.
     */
    abstract public function handle(): void;

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $e): void
    {
        // Override in subclass to handle failure
    }

    /**
     * Dispatch the job to the queue.
     */
    public static function dispatch(...$args): int
    {
        $job = new static(...$args);
        return App::getInstance()->get('queue')->push($job, $job->getQueue());
    }

    /**
     * Dispatch the job with a delay.
     */
    public static function dispatchLater(int $delay, ...$args): int
    {
        $job = new static(...$args);
        return App::getInstance()->get('queue')->later($delay, $job, $job->getQueue());
    }

    /**
     * Get the number of times the job may be attempted.
     */
    public function getTries(): int
    {
        return $this->tries;
    }

    /**
     * Get the number of seconds to wait before retrying.
     */
    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }

    /**
     * Get the queue on which the job should be placed.
     */
    public function getQueue(): ?string
    {
        return $this->queue;
    }

    /**
     * Set the queue on which the job should be placed.
     */
    public function onQueue(string $queue): static
    {
        $this->queue = $queue;
        return $this;
    }
}
