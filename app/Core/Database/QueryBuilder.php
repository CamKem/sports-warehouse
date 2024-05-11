<?php

namespace app\Core\Database;

class QueryBuilder
{

    protected static self|null $instance = null;
    protected string $query;
    protected array $conditions = [];
    protected array $with = [];

    public function __construct(
        protected string $table,
    )
    {
        $this->query = "select * from $this->table";
    }

    public static function getInstance(): static
    {
        return static::$instance ??= new static();
    }

    public static function where(string $column, mixed $operator, mixed $value = null): static
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = "=";
        }
        $queryBuilder = self::getInstance();
        $queryBuilder->conditions[] = [$column, $operator, $value];
        return $queryBuilder;
    }

    public static function with(string $relation): static
    {
        $queryBuilder = self::getInstance();
        $queryBuilder->with[] = $relation;
        return $queryBuilder;
    }

    public function toSql(): string
    {
        if (str_starts_with(trim($this->query), 'INSERT')) {
            return $this->query;
        }

        if (!empty($this->with)) {
            $modelInstance = new static();
            foreach ($this->with as $relation) {
                $related = $modelInstance->$relation();
                $relatedTable = $related->getRelatedTable();
                $foreignColumn = $related->getForeignKey();

                $columns = $this->db->execute("SHOW COLUMNS FROM {$relatedTable}")->get();

                $prefixedColumns = array_map(static function ($column) use ($relatedTable, $relation) {
                    return "{$relatedTable}.{$column['Field']} as {$relation}_{$column['Field']}";
                }, $columns);

                $this->query = str_replace("select *", "select {$this->table}.*, " . implode(", ", $prefixedColumns), $this->query);
                $this->query .= " inner join {$relatedTable} on {$this->table}.{$foreignColumn} = {$relatedTable}.id";
            }
        }

        if (!empty($this->conditions)) {
            $this->query .= " where ";
            foreach ($this->conditions as $index => $condition) {
                if ($index > 0) {
                    $this->query .= " and ";
                }
                if (!empty($this->with)) {
                    $this->query .= "{$this->table}.{$condition[0]} {$condition[1]} :{$condition[0]}";
                } else {
                    $this->query .= "{$condition[0]} {$condition[1]} :{$condition[0]}";
                }
            }
        }

        return $this->query;
    }

    public static function create(array $data): static
    {
        $instance = self::getInstance();
        $columns = implode(", ", array_keys($data));
        $placeholders = array_map(fn($key) => ":{$key}", array_keys($data));
        $values = implode(", ", $placeholders);

        $instance->query = "INSERT INTO {$instance->table} ({$columns}) VALUES ({$values})";
        $instance->conditions = array_map(fn($key) => [$key, '=', $data[$key]], array_keys($data));

        return $instance;
    }

    public function getBindings(): array
    {
        $bindings = [];
        foreach ($this->conditions as $condition) {
            $bindings[$condition[0]] = $condition[2];
        }
        return $bindings;
    }

    public function insert(array $data): bool
    {
        $columns = implode(", ", array_keys($data));
        $values = implode(", ", array_map(fn($value) => ":$value", array_keys($data)));
        $this->query = "insert into $this->table ($columns) values ($values)";
        return $this->query;
    }

}