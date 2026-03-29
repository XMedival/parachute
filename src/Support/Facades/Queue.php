<?php

namespace Parachute\Support\Facades;

use Parachute\Base\App;
use Parachute\Queue\Job;

class Queue
{
    public static function __callStatic($method, $args)
    {
        return App::getInstance()->get('queue')->$method(...$args);
    }

    /**
     * Push a job onto the queue.
     */
    public static function push(Job $job, ?string $queue = null): int
    {
        return App::getInstance()->get('queue')->push($job, $queue);
    }

    /**
     * Push a job onto the queue with a delay.
     */
    public static function later(int $delay, Job $job, ?string $queue = null): int
    {
        return App::getInstance()->get('queue')->later($delay, $job, $queue);
    }

    /**
     * Retry a failed job.
     */
    public static function retry(int $failedJobId): bool
    {
        return App::getInstance()->get('queue')->retry($failedJobId);
    }

    /**
     * Retry all failed jobs.
     */
    public static function retryAll(): int
    {
        return App::getInstance()->get('queue')->retryAll();
    }
}
