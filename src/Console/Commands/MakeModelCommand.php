<?php

namespace Parachute\Console\Commands;


class MakeModelCommand implements Command
{
    public function handle(array $args, string $basePath): int
    {
        if (empty($args)) {
            echo "\033[31m✗ Please provide a model name.\033[0m\n";
            echo "Usage: \033[33m./radio make:model <ModelName>\033[0m\n";
            return 1;
        }

        $modelName = strtoupper($args[0][0]) . substr($args[0], 1);
        $modelPath = $basePath . "/app/Models/{$modelName}.php";

        if (file_exists($modelPath)) {
            echo "\033[31m✗ Model already exists: {$modelName}\033[0m\n";
            return 1;
        }

        $modelTemplate = "<?php

use Parachute\Database\Model;

class {$modelName} extends Model
{
    
}";

        $dir = dirname($modelPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        echo "\033[36mCreating model: {$modelName}...\033[0m\n";
        echo "test: {$modelPath}\n";
        touch($modelPath);
        file_put_contents($modelPath, $modelTemplate);
        echo "\033[32m✓ Model created: {$modelName}\033[0m\n";
        return 0;
    }
}
