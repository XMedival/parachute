<?php

namespace Parachute\Support\Facades;

use Parachute\Base\App;

class Route
{
    public static function __callStatic($method, $args)
    {
        return App::getInstance()->make('router')->$method(...$args);
    }
}
