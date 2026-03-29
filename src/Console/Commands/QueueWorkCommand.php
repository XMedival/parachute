<?php

namespace Parachute\Console\Commands;

use Parachute\Base\App;
use Parachute\Queue\Worker;

class QueueWorkCommand implements Command
{
    public function handle(array $args, string $basePath): int
    {
        $options = $this->parseOptions($args);

        $queue = $options['queue'] ?? 'default';
        $sleep = (int) ($options['sleep'] ?? 3);
        $timeout = (int) ($options['timeout'] ?? 60);
        $memory = (int) ($options['memory'] ?? 128);

        echo "\033[32m✓ Queue worker started\033[0m\n";
        echo "  Queue: {$queue}\n";
        echo "  Sleep: {$sleep}s\n";
        echo "  Timeout: {$timeout}s\n";
        echo "  Memory limit: {$memory}MB\n";
        echo "\n";

        $queueService = App::getInstance()->get('queue');
        $worker = new Worker($queueService);

        $worker->daemon($queue, [
            'sleep' => $sleep,
            'timeout' => $timeout,
            'memory' => $memory,
        ]);

        return 0;
    }

    /**
     * Parse command line options.
     */
    protected function parseOptions(array $args): array
    {
        $options = [];

        foreach ($args as $arg) {
            if (str_starts_with($arg, '--')) {
                $arg = substr($arg, 2);
                if (str_contains($arg, '=')) {
                    [$key, $value] = explode('=', $arg, 2);
                    $options[$key] = $value;
                } else {
                    $options[$arg] = true;
                }
            }
        }

        return $options;
    }
}
