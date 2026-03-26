<?php

namespace Parachute\Database;

use Parachute\Base\App;

class ModelQueryBuilder
{
    protected QueryBuilder $query;
    protected string $model;

    public function __construct(string $model)
    {
        $this->model = $model;
        $this->query = App::getInstance()->get('db')->connection()->table((new $model)->getTable());
    }

    public function where(...$args): static
    {
        $this->query->where(...$args);
        return $this;
    }

    public function get(): array
    {
        $rows = $this->query->get();
        return array_map(function ($row) {
            $model = new $this->model((array)$row);
            $model->exists = true;
            return $model;
        }, $rows);
    }

    public function first(): ?object
    {
        $row = $this->query->first();
        return $row ? new $this->model((array)$row) : null;
    }

    public function limit(int $count): array
    {
        $rows = $this->query->limit($count)->get();
        return array_map(fn($row) => new $this->model((array)$row), $rows);
    }
}
