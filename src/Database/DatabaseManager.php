<?php

namespace Parachute\Database;

use Parachute\Database\Connection\Connection;
use Parachute\Contracts\Database\ConnectorContract;
use Parachute\Contracts\Database\ConnectionContract;
use Parachute\Database\Connector\SQLiteConnector;

class DatabaseManager
{
    protected array $connections = [];
    protected array $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function connection(?string $name = null): ConnectionContract
    {
        $name = $name ?: $this->getDefaultConnection();

        if (!isset($this->connections[$name])) {
            $this->connections[$name] = $this->makeConnection($name);
        }

        return $this->connections[$name];
    }

    protected function makeConnection(string $name): Connection
    {
        $config = $this->config['connections'][$name];
        $connector = $this->getConnector($config['driver']);
        $pdo = $connector->connect($config);

        return new Connection($pdo, $config['database'], $config['prefix'] ?? '', $config);
    }

    protected function getConnector(string $driver): ConnectorContract
    {
        return match ($driver) {
            'sqlite' => new SQLiteConnector(),
            /* 'mysql' => new MySQLConnector(), */
            default => throw new \Exception("Unknown driver: $driver"),
        };
    }

    protected function getDefaultConnection(): string
    {
        return $this->config['default'] ?? 'sqlite';
    }
}
