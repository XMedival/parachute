<?php

namespace Parachute\Console\Commands;

class ServeCommand implements Command
{
    public function handle(array $args, string $basePath): int
    {
        $port = $args[0] ?? '8000';
        echo "\033[32m✓ Starting development server on http://localhost:{$port}\033[0m\n";
        echo "Press Ctrl+C to stop the server.\n";

        // Use PHP's built-in server for simplicity
        passthru("php -S localhost:{$port} -t public public/index.php");

        return 0;
    }
}
