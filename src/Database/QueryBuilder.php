<?php

namespace Parachute\Database;

use Parachute\Database\Connection\Connection;
use Parachute\Database\Raw;

class QueryBuilder
{
    protected Connection $connection;
    protected string $table;
    protected array $wheres = [];
    protected array $bindings = [];
    protected array $columns = ['*'];
    protected ?int $limit = null;
    protected ?int $offset = null;
    protected array $orders = [];

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function select(array|string $columns = ['*']): self
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function where(string $column, ?string $operator = null, mixed $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $isRaw = $value instanceof Raw;

        $this->wheres[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'type' => $isRaw ? 'raw' : 'basic',
        ];
        if (!$isRaw) {
            $this->bindings[] = $value;
        }

        return $this;
    }

    public function whereNull(string $column): self
    {
        $this->wheres[] = [
            'column' => $column,
            'type' => 'null',
        ];
        return $this;
    }

    public function whereNotNull(string $column): self
    {
        $this->wheres[] = [
            'column' => $column,
            'type' => 'notNull',
        ];
        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->orders[] = compact('column', 'direction');
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function get(): array
    {
        $sql = $this->buildSelect();
        return $this->connection->select($sql, $this->bindings);
    }

    public function first(): ?array
    {
        $row = $this->limit(1)->get();
        return $row[0] ?? null;
    }

    public function find(int|string $id): ?array
    {
        return $this->where('id', $id)->first();
    }

    public function insert(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";

        return $this->connection->insert($sql, array_values($data));
    }

    public function update(array $data): int
    {
        $sets = implode(', ', array_map(fn($col) => "$col = ?", array_keys($data)));
        $bindings = array_merge(array_values($data), $this->bindings);

        $sql = "UPDATE {$this->table} SET $sets" . $this->buildWheres();

        return $this->connection->update($sql, $bindings);
    }

    protected function buildSelect(): string
    {
        $columns = implode(', ', $this->columns);
        $sql = "SELECT {$columns} FROM {$this->table}";

        $sql .= $this->buildWheres();
        $sql .= $this->buildOrders();

        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }

    protected function buildWheres(): string
    {
        if (empty($this->wheres)) {
            return '';
        }

        $clauses = array_map(function ($w) {
            if (($w['type'] ?? null) === 'null') {
                return "{$w['column']} IS NULL";
            }
            if (($w['type'] ?? null) === 'notNull') {
                return "{$w['column']} IS NOT NULL";
            }
            if (($w['type'] ?? null) === 'raw') {
                return "{$w['column']} {$w['operator']} {$w['value']}";
            }
            return "{$w['column']} {$w['operator']} ?";
        }, $this->wheres);

        return ' WHERE ' . implode(' AND ', $clauses);
    }

    protected function buildOrders(): string
    {
        if (empty($this->orders)) {
            return '';
        }

        $clauses = array_map(
            fn($o) => "{$o['column']} {$o['direction']}",
            $this->orders
        );

        return ' ORDER BY ' . implode(', ', $clauses);
    }
}
