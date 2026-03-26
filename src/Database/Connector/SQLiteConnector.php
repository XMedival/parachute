<?php

namespace Parachute\Database\Connector;

use Exception;
use Parachute\Contracts\Database\ConnectorContract;

class SQLiteConnector extends Connector implements ConnectorContract
{
    public function connect(array $config): \PDO
    {
        $options = $this->getOptions($config);

        $path = $this->parseDatabasePath($config['database']);

        $connection = $this->createConnection("sqlite:{$path}", $config, $options);

        $this->configurePragmas($connection, $config);
        $this->configureForeignKeyConstraints($connection, $config);
        $this->configureBusyTimeout($connection, $config);
        $this->configureJournalMode($connection, $config);
        $this->configureSynchronous($connection, $config);

        return $connection;
    }

    protected function parseDatabasePath(string $path): string
    {
        $database = $path;

        if (
            $path === ':memory:' ||
            str_contains($path, '?mode=memory') ||
            str_contains($path, '&mode=memory')
        ) {
            return $path;
        }

        $path = realpath($path) ?: realpath(base_path($path));

        if ($path === false) {
            throw new Exception('database does not exsist: ' . $database);
        }

        return $path;
    }

    protected function configurePragmas($connection, array $config): void
    {
        if (! isset($config['pragmas'])) {
            return;
        }

        foreach ($config['pragmas'] as $pragma => $value) {
            $connection->prepare("pragma {$pragma} = {$value}")->execute();
        }
    }

    protected function configureForeignKeyConstraints($connection, array $config): void
    {
        if (! isset($config['foreign_key_constraints'])) {
            return;
        }

        $foreignKeys = $config['foreign_key_constraints'] ? 1 : 0;

        $connection->prepare("pragma foreign_keys = {$foreignKeys}")->execute();
    }

    protected function configureBusyTimeout($connection, array $config): void
    {
        if (! isset($config['busy_timeout'])) {
            return;
        }

        $connection->prepare("pragma busy_timeout = {$config['busy_timeout']}")->execute();
    }

    protected function configureJournalMode($connection, array $config): void
    {
        if (! isset($config['journal_mode'])) {
            return;
        }

        $connection->prepare("pragma journal_mode = {$config['journal_mode']}")->execute();
    }

    protected function configureSynchronous($connection, array $config): void
    {
        if (! isset($config['synchronous'])) {
            return;
        }

        $connection->prepare("pragma synchronous = {$config['synchronous']}")->execute();
    }
}
