<?php

namespace Parachute\Contracts\Database;

interface ConnectorContract
{
    public function connect(array $config): \PDO;
}
