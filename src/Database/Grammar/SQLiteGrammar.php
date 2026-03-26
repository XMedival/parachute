<?php

namespace Parachute\Database\Grammar;

use Parachute\Contracts\Database\GrammarContract;
use Parachute\Database\Schema\Blueprint;
use Parachute\Database\Schema\Column;

class SQLiteGrammar implements GrammarContract
{
    public function compileTableExists(string $table): array
    {
        return [
            "SELECT name FROM sqlite_master WHERE type='table' AND name = ?",
            [$table]
        ];
    }

    public function compileGetTables(): string
    {
        return "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'";
    }

    public function compileColumn(Column $column): string
    {
        $sql = $column->name . ' ' . $column->type;

        if ($column->length) {
            $sql .= "({$column->length})";
        }

        if ($column->primary) {
            $sql .= ' PRIMARY KEY';
        }

        if ($column->autoIncrement) {
            $sql .= ' AUTOINCREMENT';
        }

        if (!$column->nullable && !$column->primary) {
            $sql .= ' NOT NULL';
        }

        if ($column->hasDefault) {
            $default = $column->default;

            if (is_string($default) && !in_array($default, ['CURRENT_TIMESTAMP', 'NULL'])) {
                $default = "'{$default}'";
            } elseif (is_bool($default)) {
                $default = $default ? 1 : 0;
            } elseif (is_null($default)) {
                $default = 'NULL';
            }

            $sql .= " DEFAULT {$default}";
        }

        return $sql;
    }

    public function compileCreateTable(Blueprint $blueprint): string
    {
        $columns = array_map(
            fn(Column $col) => $this->compileColumn($col),
            $blueprint->getColumns()
        );

        return "CREATE TABLE {$blueprint->getTable()} (" . implode(', ', $columns) . ")";
    }

    public function compileDropTable(string $table): string
    {
        return "DROP TABLE {$table}";
    }

    public function compileDropTableIfExists(string $table): string
    {
        return "DROP TABLE IF EXISTS {$table}";
    }

    public function compileNow(): string
    {
        return "CURRENT_TIMESTAMP";
    }
}
