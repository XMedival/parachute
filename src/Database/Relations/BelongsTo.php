<?php

namespace Parachute\Database\Relations;

use Parachute\Database\Model;

class BelongsTo
{
    public function __construct(
        protected Model $parent,
        protected string $related,
        protected string $foreignKey,
    ) {}

    public function get(): ?Model
    {
        $id = $this->parent->{$this->foreignKey};
        return $this->related::find($id);
    }
}
