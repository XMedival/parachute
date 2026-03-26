<?php

namespace Parachute\Database\Connector;

use PDO;

class Connector
{
    protected array $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    public function createConnection(string $dsn, array $config, array $options): \PDO
    {
        $username = $config['username'] ?? null;
        $password = $config['password'] ?? null;

        return $this->createPdoConnection($dsn, $username, $password, $options);
    }

    protected function createPdoConnection(string $dsn, ?string $username, ?string $password, array $options): \PDO
    {
        return version_compare(phpversion(), '8.4.0', '<')
            ? new PDO($dsn, $username, $password, $options)
            : PDO::connect($dsn, $username, $password, $options);
        /* * @phpstan-ignore staticMethod.notFound (PHP 8.4)  */
    }

    public function getOptions(array $config)
    {
        $options = $config['options'] ?? [];

        return array_diff_key($this->options, $options) + $options;
    }

    public function getDefaultOptions()
    {
        return $this->options;
    }

    public function setDefaultOptions(array $options)
    {
        $this->options = $options;
    }
}
