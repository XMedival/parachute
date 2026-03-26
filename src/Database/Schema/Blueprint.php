<?php

namespace Parachute\Database\Schema;

class Blueprint
{
    protected string $table;
    protected array $columns = [];

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function id(string $name = 'id'): static
    {
        $this->columns[] = new Column(
            name: $name,
            type: 'INTEGER',
            primary: true,
            autoIncrement: true,
        );
        return $this;
    }

    public function string(string $name, int $length = 255): static
    {
        $this->columns[] = new Column(name: $name, type: 'VARCHAR', length: $length);
        return $this;
    }

    public function text(string $name): static
    {
        $this->columns[] = new Column(name: $name, type: 'TEXT');
        return $this;
    }

    public function integer(string $name): static
    {
        $this->columns[] = new Column(name: $name, type: 'INTEGER');
        return $this;
    }

    public function unsignedInteger(string $name): static
    {
        $this->columns[] = new Column(name: $name, type: 'INTEGER', unsigned: true);
        return $this;
    }

    public function float(string $name): static
    {
        $this->columns[] = new Column(name: $name, type: 'FLOAT');
        return $this;
    }

    public function decimal(string $name, int $precision = 8, int $scale = 2): static
    {
        $this->columns[] = new Column(name: $name, type: "DECIMAL({$precision},{$scale})");
        return $this;
    }

    public function boolean(string $name): static
    {
        $this->columns[] = new Column(name: $name, type: 'BOOLEAN');
        return $this;
    }

    public function date(string $name): static
    {
        $this->columns[] = new Column(name: $name, type: 'DATE');
        return $this;
    }

    public function datetime(string $name): static
    {
        $this->columns[] = new Column(name: $name, type: 'DATETIME');
        return $this;
    }

    public function timestamp(string $name): static
    {
        $this->columns[] = new Column(name: $name, type: 'TIMESTAMP');
        return $this;
    }

    public function timestamps(): static
    {
        $this->columns[] = new Column(
            name: 'created_at',
            type: 'TIMESTAMP',
            nullable: true,
            default: 'CURRENT_TIMESTAMP',
            hasDefault: true,
        );
        $this->columns[] = new Column(
            name: 'updated_at',
            type: 'TIMESTAMP',
            nullable: true,
            default: 'CURRENT_TIMESTAMP',
            hasDefault: true,
        );
        return $this;
    }

    public function nullable(): static
    {
        $this->lastColumn()->nullable = true;
        return $this;
    }

    public function default(mixed $value): static
    {
        $this->lastColumn()->default = $value;
        $this->lastColumn()->hasDefault = true;
        return $this;
    }

    public function unsigned(): static
    {
        $this->lastColumn()->unsigned = true;
        return $this;
    }

    protected function lastColumn(): Column
    {
        return $this->columns[array_key_last($this->columns)];
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getTable(): string
    {
        return $this->table;
    }
}
