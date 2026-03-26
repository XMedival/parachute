<?php

namespace Parachute\Support\Facades;

use Parachute\Base\App;
use Parachute\Database\Raw;

class DB
{
    public static function __callStatic($method, $args)
    {
        return App::getInstance()->get('db')->connection()->$method(...$args);
    }

    public static function connection(?string $name = null)
    {
        return App::getInstance()->get('db')->connection($name);
    }

    public static function raw(string $value): Raw
    {
        return new Raw($value);
    }

    public static function now(): Raw
    {
        $grammar = App::getInstance()->get('db')->connection()->getGrammar();
        return new Raw($grammar->compileNow());
    }
}
