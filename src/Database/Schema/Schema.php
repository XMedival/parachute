<?php

namespace Parachute\Database\Schema;

use Parachute\Contracts\Database\GrammarContract;
use Parachute\Database\Connection\Connection;

class Schema
{
    protected Connection $connection;
    protected GrammarContract $grammar;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->grammar = $connection->getGrammar();
    }

    public function create(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);

        $sql = $this->grammar->compileCreateTable($blueprint);
        $this->connection->statement($sql);
    }

    public function createIfNotExists(string $table, callable $callback): void
    {
        if ($this->hasTable($table)) {
            return;
        }

        $this->create($table, $callback);
    }

    public function drop(string $table): void
    {
        $sql = $this->grammar->compileDropTable($table);
        $this->connection->statement($sql);
    }

    public function dropIfExists(string $table): void
    {
        $sql = $this->grammar->compileDropTableIfExists($table);
        $this->connection->statement($sql);
    }

    public function hasTable(string $table): bool
    {
        return $this->connection->hasTable($table);
    }

    public function rename(string $from, string $to): void
    {
        $this->connection->statement("ALTER TABLE {$from} RENAME TO {$to}");
    }
}
