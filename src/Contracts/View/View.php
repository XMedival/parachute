<?php

namespace Parachute\Contracts\View;

use Parachute\Contracts\Support\Renderable;

interface View extends Renderable
{
    public function name(): string;
    public function with(array $data): self;
    public function getData(): array;
}
