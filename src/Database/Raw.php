<?php

namespace Parachute\Database;

class Raw
{
    public function __construct(public string $value) {}

    public function __toString(): string
    {
        return $this->value;
    }
}
