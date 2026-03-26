<?php

namespace Parachute\Console\Commands;

interface Command
{
    public function handle(array $args, string $basePath): int;
}
