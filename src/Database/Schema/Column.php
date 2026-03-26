<?php

namespace Parachute\Database\Schema;

class Column
{
    public function __construct(
        public string $name,
        public string $type,
        public bool $nullable = false,
        public mixed $default = null,
        public bool $hasDefault = false,
        public bool $autoIncrement = false,
        public bool $primary = false,
        public ?int $length = null,
        public bool $unsigned = false,
    ) {}
}
