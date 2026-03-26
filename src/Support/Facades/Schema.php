<?php

namespace Parachute\Support\Facades;

use Parachute\Database\Schema\Schema as SchemaBuilder;

class Schema
{
    public static function __callStatic($method, $args)
    {
        $connection = app('db')->connection();
        $schema = new SchemaBuilder($connection);

        return $schema->$method(...$args);
    }
}
