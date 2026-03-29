<?php

namespace Parachute\Console\Commands;

use Parachute\Base\App;

class QueueRetryCommand implements Command
{
    public function handle(array $args, string $basePath): int
    {
        if (empty($args)) {
            echo "\033[31m✗ Please provide a failed job ID or 'all'.\033[0m\n";
            echo "Usage: \033[33m./radio queue:retry <id|all>\033[0m\n";
            return 1;
        }

        $queue = App::getInstance()->get('queue');
        $target = $args[0];

        if ($target === 'all') {
            $count = $queue->retryAll();
            echo "\033[32m✓ Retried {$count} failed job(s)\033[0m\n";
        } else {
            $id = (int) $target;
            if ($queue->retry($id)) {
                echo "\033[32m✓ Retried failed job #{$id}\033[0m\n";
            } else {
                echo "\033[31m✗ Failed job #{$id} not found\033[0m\n";
                return 1;
            }
        }

        return 0;
    }
}
