<?php

namespace Parachute\Console\Commands;

class HelpCommand implements Command
{
    public function handle(array $args, string $basePath): int
    {
        echo "Available commands:\n";
        echo "  \033[33mserve\033[0m             Start the development server\n";
        echo "  \033[33mmake:model\033[0m        Create a new model\n";
        echo "  \033[33mmake:migration\033[0m    Create a new migration\n";
        echo "  \033[33mmake:job\033[0m          Create a new job class\n";
        echo "  \033[33mmigrate\033[0m           Run database migrations\n";
        echo "  \033[33mmigrate:fresh\033[0m     Drop all tables and re-run migrations\n";
        echo "  \033[33mmigrate:rollback\033[0m  Rollback the last migration batch\n";
        echo "  \033[33mqueue:table\033[0m       Create the queue database migrations\n";
        echo "  \033[33mqueue:work\033[0m        Start processing jobs on the queue\n";
        echo "  \033[33mqueue:retry\033[0m       Retry a failed job\n";
        echo "  \033[33mhelp\033[0m              Display this help message\n";
        return 0;
    }
}
