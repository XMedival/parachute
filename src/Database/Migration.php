<?php

namespace Parachute\Database;

abstract class Migration
{
    public function shouldRun(): bool
    {
        return true;
    }
}
