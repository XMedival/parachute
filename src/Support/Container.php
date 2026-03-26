<?php

namespace Parachute\Support;

class Container
{
    protected array $bindings = [];
    protected array $instances = [];

    public function bind($abstract, $concrete)
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function singleton($abstract, $concrete)
    {
        $this->bindings[$abstract] = function () use ($abstract, $concrete) {
            if (!isset($this->instances[$abstract])) {
                $this->instances[$abstract] = $concrete($this);
            }
            return $this->instances[$abstract];
        };
    }

    public function get($abstract)
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]($this);
        }
        throw new \Exception("No binding found for {$abstract}");
    }

    public function instance($abstract, $instance)
    {
        $this->instances[$abstract] = $instance;
        $this->bindings[$abstract] = fn() => $instance;
    }

    public function make($abstract)
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]();
        }
        throw new \Exception("No binding found for {$abstract}");
    }
}
