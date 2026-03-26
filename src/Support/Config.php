<?php

namespace Parachute\Support;

class Config
{
    protected array $items = [];

    public function __construct(string $path = '')
    {
        if ($path && is_dir($path)) {
            $this->loadFrom($path);
        }
    }

    public function loadFrom(string $path): void
    {
        foreach (glob($path . '/*.php') as $file) {
            $key = basename($file, '.php');
            $this->items[$key] = require $file;
        }
    }

    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->items;

        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    public function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $target = &$this->items;

        foreach (array_slice($keys, 0, -1) as $segment) {
            if (!isset($target[$segment]) || !is_array($target[$segment])) {
                $target[$segment] = [];
            }
            $target = &$target[$segment];
        }

        $target[end($keys)] = $value;
    }

    public function all(): array
    {
        return $this->items;
    }
}
