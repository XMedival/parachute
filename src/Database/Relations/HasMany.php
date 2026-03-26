<?php

namespace Parachute\Database\Relations;

use Parachute\Database\Model;

class HasMany
{
    public function __construct(
        protected Model $parent,
        protected string $related,
        protected string $foreignKey,
    ) {}

    public function get(): array
    {
        return $this->related::where($this->foreignKey, $this->parent->id)->get();
    }
}
