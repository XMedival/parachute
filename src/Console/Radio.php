<?php

namespace Parachute\Console;

class Radio
{
    protected array $argv;
    protected string $command;
    protected array $args;

    protected array $commands = [
        'serve' => \Parachute\Console\Commands\ServeCommand::class,
        'make:model' => \Parachute\Console\Commands\MakeModelCommand::class,
        'make:migration' => \Parachute\Console\Commands\MakeMigrationCommand::class,
        'migrate' => \Parachute\Console\Commands\MigrateCommand::class,
        'migrate:fresh' => \Parachute\Console\Commands\MigrateFreshCommand::class,
        'migrate:rollback' => \Parachute\Console\Commands\RollbackCommand::class,
        'help' => \Parachute\Console\Commands\HelpCommand::class,
    ];

    public function __construct(array $argv)
    {
        $this->argv = $argv;
        $this->command = $argv[1] ?? 'help';
        $this->args = array_slice($argv, 2);
    }

    public function run(): int
    {
        $this->printBanner();

        if (!isset($this->commands[$this->command])) {
            echo "\033[31m✗ Unknown command: {$this->command}\033[0m\n";
            echo "Run \033[33m./radio help\033[0m for available commands.\n";
            return 1;
        }

        $commandClass = $this->commands[$this->command];
        $command = new $commandClass();

        return $command->handle($this->args, getcwd());
    }

    protected function printBanner(): void
    {
        echo "\033[36m📻 Radio\033[0m - Parachute CLI\n\n";
    }
}
