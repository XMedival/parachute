<?php

namespace Parachute\Contracts\View;

use Parachute\Contracts\View\View;

interface Factory
{
    public function exists(string $view): bool;
    public function make(string $view, array $data = []): View;
}
