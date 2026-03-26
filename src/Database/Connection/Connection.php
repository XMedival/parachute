<?php

namespace Parachute\Database\Connection;

use Closure;
use Parachute\Contracts\Database\ConnectionContract;
use Parachute\Contracts\Database\GrammarContract;
use Parachute\Database\QueryBuilder;
use Parachute\Support\Arr;
use Parachute\Database\Grammar\SQLiteGrammar;

class Connection implements ConnectionContract
{
    protected string $database;

    protected string $tablePrefix = '';

    protected int $transactions = 0;

    protected bool $recordsModified = false;

    /**
     * The query log for the connection.
     *
     * @var array{query: string, bindings: array, time: float|null}[]
     */
    protected array $queryLog = [];

    protected bool $loggingQueries = false;

    protected bool $pretending = false;

    protected Arr $config;

    protected \PDO $pdo;

    protected GrammarContract $grammar;

    public function __construct(\PDO $pdo, string $database = '', $tablePrefix = '', array $config = [])
    {
        $this->pdo = $pdo;
        $this->database = $database;
        $this->tablePrefix = $tablePrefix;
        $this->config = new Arr($config);
        $this->grammar = $this->resolveGrammar($config['driver'] ?? 'sqlite');
    }

    public function resolveGrammar(string $driver): GrammarContract
    {
        return match ($driver) {
            'sqlite' => new SQLiteGrammar(),
            /* 'mysql' => new MySqlGrammar(), */
            /* 'pgsql' => new PostgresGrammar(), */
            /* 'sqlsrv' => new SqlServerGrammar(), */
            default => throw new \InvalidArgumentException("Unsupported driver: $driver"),
        };
    }

    public function getGrammar(): GrammarContract
    {
        return $this->grammar;
    }

    public function hasTable(string $table): bool
    {
        [$sql, $bindings] = $this->grammar->compileTableExists($table);
        return count($this->select($sql, $bindings)) > 0;
    }

    public function getTables(): array
    {
        $sql = $this->grammar->compileGetTables();
        return array_column($this->select($sql), 'name');
    }

    public function getName(): string
    {
        return $this->config['name'];
    }

    public function getConfig(): array
    {
        return $this->config->all();
    }

    public function getConnection()
    {
        return $this->pdo;
    }

    protected function run(string $query, array $bindings = []): \PDOStatement
    {
        $startTime = microtime(true);

        $statement = $this->pdo->prepare($query);
        $statement->execute($bindings);

        $this->logQuery($query, $bindings, microtime(true) - $startTime);

        return $statement;  // return statement, let caller decide how to fetch                                                                                                                   
    }

    public function select(string $query, array $bindings = []): array
    {
        return $this->run($query, $bindings)->fetchAll();
    }

    public function insert(string $query, array $bindings = []): int
    {
        $this->run($query, $bindings);
        return (int) $this->pdo->lastInsertId() > 0;
    }

    public function update(string $query, array $bindings = []): int
    {
        return $this->run($query, $bindings)->rowCount();
    }

    public function delete(string $query, array $bindings = []): int
    {
        return $this->run($query, $bindings)->rowCount();
    }

    public function statement(string $query, array $bindings = []): int
    {
        return $this->run($query, $bindings)->rowCount();
    }

    public function table(string $table): QueryBuilder
    {
        return (new QueryBuilder($this))->table($table);
    }

    public function logQuery($query, $bindings, $time = null)
    {
        if ($this->loggingQueries) {
            $this->queryLog[] = compact('query', 'bindings', 'time');
        }
    }

    public function getQueryLog(): array
    {
        return $this->queryLog;
    }

    public function beginTransaction()
    {
        $this->transactions++;
        return $this->pdo->beginTransaction();
    }

    public function commit()
    {
        if ($this->transactions > 0) {
            $this->transactions--;
            return $this->pdo->commit();
        }
        throw new \LogicException('No active transaction to commit.');
    }

    public function rollBack()
    {
        if ($this->transactions > 0) {
            $this->transactions--;
            return $this->pdo->rollBack();
        }
        throw new \LogicException('No active transaction to roll back.');
    }

    public function transaction(Closure $callback)
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    public function isPretending(): bool
    {
        return $this->pretending;
    }

    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    public function setPdo(\PDO $pdo): void
    {
        $this->pdo = $pdo;
    }
}
