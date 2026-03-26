<?php

namespace Parachute\Console\Commands;

class MakeMigrationCommand implements Command
{
    public function handle(array $args, string $basePath): int
    {
        if (empty($args)) {
            echo "\033[31m✗ Please provide a migration name.\033[0m\n";
            echo "Usage: \033[33m./radio make:migration <MigrationName>\033[0m\n";
            return 1;
        }

        $migrationName = to_snake_case($args[0]);
        $timestamp = date('Y_m_d_His');
        $migrationFileName = "{$timestamp}_{$migrationName}.php";
        $migrationPath = $basePath . "/database/migrations/{$migrationFileName}";

        if (file_exists($migrationPath)) {
            echo "\033[31m✗ Migration already exists: {$migrationFileName}\033[0m\n";
            return 1;
        }

        $tableName = $this->inferTableName($migrationName);

        $migrationTemplate = "<?php

use Parachute\Support\Facades\Schema;
use Parachute\Database\Blueprint;
use Parachute\Database\Migration;

return new class extends Migration
{
    /*
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Define your migration logic here
        Schema::create('$tableName', function (\$table) {
            \$table->id();
            \$table->timestamps();
        });
    }

    /*
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('$tableName');
    }
};";

        $dir = dirname($migrationPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        echo "\033[36mCreating migration: {$migrationFileName}...\033[0m\n";
        touch($migrationPath);
        file_put_contents($migrationPath, $migrationTemplate);
        echo "\033[32m✓ Migration created: {$migrationFileName}\033[0m\n";
        return 0;
    }

    protected function inferTableName(string $migrationName): string
    {
        if (str_contains($migrationName, 'create_') && str_contains($migrationName, '_table')) {
            return str_replace(['create_', '_table'], '', $migrationName);
        }
        return to_snake_case(str_replace(' ', '_', $migrationName));
    }
}
