<?php

namespace Parachute\Database;

use Parachute\Base\App;
use Parachute\Support\Facades\DB;

abstract class Model
{
    protected ?string $table = null;
    protected string $primaryKey = 'id';
    protected array $attributes = [];
    protected bool $exists = false;
    protected static array $booted = [];
    protected array $relations = [];

    // Structure: ['App\Models\User' => ['creating' => [callable, callable], 'created' => [...]]]
    protected static array $events = [];

    public function __construct(array $attributes = [])
    {
        $this->bootIfNotBooted();
        $this->fill($attributes);
    }

    protected function bootIfNotBooted(): void
    {
        $class = static::class;

        if (!isset(static::$booted[$class])) {
            static::$booted[$class] = true;
            $this->boot();
        }
    }

    protected function boot(): void
    {
        // Child classes can override this method to perform booting actions
    }

    public static function creating(callable $callback): void
    {
        $class = static::class;
        static::$events[$class]['creating'][] = $callback;
    }

    public static function created(callable $callback): void
    {
        $class = static::class;
        static::$events[$class]['created'][] = $callback;
    }

    public static function updating(callable $callback): void
    {
        $class = static::class;
        static::$events[$class]['updating'][] = $callback;
    }

    public static function updated(callable $callback): void
    {
        $class = static::class;
        static::$events[$class]['updated'][] = $callback;
    }

    protected function fill(array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }
    }

    protected function fireEvent(string $event): bool
    {
        $class = static::class;

        if (isset(static::$events[$class][$event])) {
            foreach (static::$events[$class][$event] as $callback) {
                if ($callback($this) === false) {
                    return false; // Stop if any callback returns false
                }
            }
        }

        return true;
    }

    protected function guessTableName(): string
    {
        $class = (new \ReflectionClass($this))->getShortName();
        $snake = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $class));

        return pluralize($snake);
    }

    public function getTable(): string
    {
        return $this->table ??= $this->guessTableName();
    }

    public function save(): bool
    {
        if (!$this->fireEvent('saving')) return false;

        if ($this->exists) {
            if (!$this->fireEvent('updating')) return false;
            $this->performUpdate();
            if (!$this->fireEvent('updated')) return false;
        } else {
            if (!$this->fireEvent('creating')) return false;
            $this->performInsert();
            $this->exists = true; // Mark as existing after insertion
            if (!$this->fireEvent('created')) return false;
        }

        $this->fireEvent('saved');
        return true;
    }

    protected function performInsert(): void
    {
        $id = DB::table($this->getTable())->insert($this->attributes);
        $this->attributes[$this->primaryKey] = $id;
    }

    protected function performUpdate(): void
    {
        DB::table($this->getTable())
            ->where($this->primaryKey, $this->attributes[$this->primaryKey])
            ->update($this->attributes);
    }

    public static function all(): array
    {
        $class = static::class;
        $table = (new $class)->getTable();
        $rows = DB::table($table)->get();

        return array_map(fn($row) => new $class((array)$row), $rows);
    }

    public static function find($id): ?self
    {
        $class = static::class;
        $table = (new $class)->getTable();
        $row = DB::table($table)->where((new $class)->primaryKey, $id)->first();

        return $row ? new $class((array)$row) : null;
    }

    public static function where(string $column, $value): ModelQueryBuilder
    {
        return static::query()->where($column, $value);
    }

    public static function query(): ModelQueryBuilder
    {
        return new ModelQueryBuilder(static::class);
    }


    public static function first(): ?self
    {
        $class = static::class;
        $table = (new $class)->getTable();
        $row = DB::table($table)->first();

        return $row ? new $class((array)$row) : null;
    }

    public static function limit(int $count): array
    {
        $class = static::class;
        $table = (new $class)->getTable();
        $rows = DB::table($table)->limit($count)->get();

        return array_map(fn($row) => new $class((array)$row), $rows);
    }

    public function delete(): bool
    {
        if (!$this->exists) return false;

        if (!$this->fireEvent('deleting')) return false;

        DB::table($this->getTable())
            ->where($this->primaryKey, $this->attributes[$this->primaryKey])
            ->delete();

        $this->exists = false; // Mark as deleted
        $this->fireEvent('deleted');
        return true;
    }

    public function hasMany(string $related, ?string $foreignKey = null): Relations\HasMany
    {
        $foreignKey = $foreignKey ?? $this->guessForeignKey();
        return new Relations\HasMany($this, $related, $foreignKey);
    }

    public function belongsTo(string $related, ?string $foreignKey = null): Relations\BelongsTo
    {
        $foreignKey = $foreignKey ?? $this->guessForeignKey();
        return new Relations\BelongsTo($this, $related, $foreignKey);
    }

    protected function guessForeignKey(): string
    {
        $class = (new \ReflectionClass($this))->getShortName();
        return strtolower($class) . '_id';
    }

    protected function getRelation(string $key): mixed
    {
        $relation = $this->$key();
        $result = $relation->get();
        $this->relations[$key] = $result;
        return $result;
    }

    public function __get($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }
        if (array_key_exists($key, $this->relations)) {
            return $this->relations[$key];
        }
        if (method_exists($this, $key)) {
            return $this->getRelation($key);
        }
        return null;
    }

    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }
}
