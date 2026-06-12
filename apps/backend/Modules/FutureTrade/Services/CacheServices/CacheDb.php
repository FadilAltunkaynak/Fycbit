<?php

namespace Modules\FutureTrade\Services\CacheServices;

use Illuminate\Support\Facades\Redis;
use stdClass;

class CacheDb
{
    private string $connection = 'future_trade_cache';
    private string $table;
    private string $idKey = 'id';
    private array $query_conditions = [];
    private array $query_keys = [];
    private bool $single = false;

    /**
     * Constructor
     * @param string $table Table/Collection name in Redis
     * @param string $idKey Primary key field (default: 'id')
     */
    public function __construct(string $table, string $idKey = 'id')
    {
        $this->table = $table;
        $this->idKey = $idKey;
    }

    /**
     * Create a new instance for a specific table
     * @param string $table
     * @param string $idKey
     * @return static
     */
    public static function table(string $table, string $idKey = 'id'): static
    {
        return new static($table, $idKey);
    }

    /**
     * Insert data as array
     * @param array $data
     * @return bool|string The ID of the inserted record
     */
    public function insert(array $data): bool|string
    {
        if (empty($data[$this->idKey])) {
            $data[$this->idKey] = $this->generateId();
        }

        $id = $data[$this->idKey];
        $key = $this->getRecordKey($id);

        // Store the record hash
        Redis::connection($this->connection)->hset($key, $data);

        // Store ID in set for fast lookups
        $this->addToIndexSet($id);

        return $id;
    }

    /**
     * Insert multiple records
     * @param array $records
     * @return array Array of inserted IDs
     */
    public function insertMany(array $records): array
    {
        $ids = [];
        foreach ($records as $record) {
            $ids[] = $this->insert($record);
        }
        return $ids;
    }

    /**
     * Find a record by ID
     * @param mixed $id
     * @return array|null
     */
    public function find(mixed $id): ?array
    {
        $key = $this->getRecordKey($id);
        $data = Redis::connection($this->connection)->hgetall($key);

        return empty($data) ? null : $data;
    }

    /**
     * Get a single value from a record by ID
     * @param mixed $id
     * @param string $field
     * @return mixed
     */
    public function findValue(mixed $id, string $field): mixed
    {
        $key = $this->getRecordKey($id);
        return Redis::connection($this->connection)->hget($key, $field);
    }

    /**
     * Get all records
     * @return array
     */
    public function all(): array
    {
        $ids = $this->getAllIds();
        $records = [];

        foreach ($ids as $id) {
            $record = $this->find($id);
            if ($record) {
                $records[] = $record;
            }
        }

        return $records;
    }

    /**
     * Query builder - where clause
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function where(string $key, mixed $value): static
    {
        $this->query_conditions[$key] = $value;
        return $this;
    }

    /**
     * Query builder - select only specific keys
     * @param string|array $keys
     * @return $this
     */
    public function select(string|array $keys): static
    {
        $this->query_keys = is_array($keys) ? $keys : [$keys];
        return $this;
    }

    /**
     * Get first result from query
     * @return array|null
     */
    public function first(): ?array
    {
        $this->single = true;
        $results = $this->get();
        $this->resetQuery();
        return $results[0] ?? null;
    }

    /**
     * Get all results from query
     * @return array
     */
    public function get(): array
    {
        $ids = $this->getAllIds();
        $results = [];

        foreach ($ids as $id) {
            $record = $this->find($id);
            if (empty($record)) {
                continue;
            }

            // Apply where conditions
            if (!$this->matchesConditions($record)) {
                continue;
            }

            // Select only specific keys if needed
            if (!empty($this->query_keys)) {
                $record = array_intersect_key($record, array_flip($this->query_keys));
            }

            $results[] = $record;

            if ($this->single) {
                break;
            }
        }

        $this->resetQuery();
        return $results;
    }

    /**
     * Update a record by ID
     * @param mixed $id
     * @param array $data
     * @return bool
     */
    public function update(mixed $id, array $data): bool
    {
        $key = $this->getRecordKey($id);

        // Check if record exists
        if (!Redis::connection($this->connection)->exists($key)) {
            return false;
        }

        // Update fields
        Redis::connection($this->connection)->hset($key, $data);

        return true;
    }

    /**
     * Update or insert a record
     * @param array $data
     * @param array $values
     * @return array|bool
     */
    public function updateOrCreate(array $data, array $values = []): array|bool
    {
        $id = $data[$this->idKey] ?? null;

        if (!$id) {
            return $this->insert(array_merge($data, $values));
        }

        $record = $this->find($id);

        if ($record) {
            $this->update($id, $values);
            return array_merge($record, $values);
        }

        return $this->insert(array_merge($data, $values));
    }

    /**
     * Delete a record by ID
     * @param mixed $id
     * @return bool
     */
    public function delete(mixed $id): bool
    {
        $key = $this->getRecordKey($id);
        $result = Redis::connection($this->connection)->del($key);
        $this->removeFromIndexSet($id);

        return $result > 0;
    }

    /**
     * Delete multiple records by IDs
     * @param array $ids
     * @return int Number of deleted records
     */
    public function deleteMany(array $ids): int
    {
        $deleted = 0;
        foreach ($ids as $id) {
            if ($this->delete($id)) {
                $deleted++;
            }
        }
        return $deleted;
    }

    /**
     * Truncate the entire table
     * @return bool
     */
    public function truncate(): bool
    {
        $ids = $this->getAllIds();
        $this->deleteMany($ids);
        Redis::connection($this->connection)->del($this->getIndexKey());

        return true;
    }

    /**
     * Count all records
     * @return int
     */
    public function count(): int
    {
        return Redis::connection($this->connection)->scard($this->getIndexKey());
    }

    /**
     * Increment a field value
     * @param mixed $id
     * @param string $field
     * @param int|float $increment
     * @return mixed
     */
    public function increment(mixed $id, string $field, int|float $increment = 1): mixed
    {
        $key = $this->getRecordKey($id);
        return Redis::connection($this->connection)->hincrbyfloat($key, $field, $increment);
    }

    /**
     * Decrement a field value
     * @param mixed $id
     * @param string $field
     * @param int|float $decrement
     * @return mixed
     */
    public function decrement(mixed $id, string $field, int|float $decrement = 1): mixed
    {
        return $this->increment($id, $field, -$decrement);
    }

    /**
     * Check if record exists by ID
     * @param mixed $id
     * @return bool
     */
    public function exists(mixed $id): bool
    {
        return Redis::connection($this->connection)->exists($this->getRecordKey($id)) > 0;
    }

    /**
     * Pluck a column from all records
     * @param string $field
     * @return array
     */
    public function pluck(string $field): array
    {
        $ids = $this->getAllIds();
        $values = [];

        foreach ($ids as $id) {
            $value = $this->findValue($id, $field);
            if ($value !== null) {
                $values[] = $value;
            }
        }

        return $values;
    }

    /**
     * Get all records as array of objects
     * @return array
     */
    public function toObjects(): array
    {
        return array_map(function ($record) {
            return (object) $record;
        }, $this->all());
    }

    // ==================== PRIVATE HELPERS ====================

    /**
     * Get the Redis key for a record
     * @param mixed $id
     * @return string
     */
    private function getRecordKey(mixed $id): string
    {
        return "{$this->table}:{$id}";
    }

    /**
     * Get the Redis key for the index set
     * @return string
     */
    private function getIndexKey(): string
    {
        return "{$this->table}:ids";
    }

    /**
     * Add ID to index set
     * @param mixed $id
     * @return void
     */
    private function addToIndexSet(mixed $id): void
    {
        Redis::connection($this->connection)->sadd($this->getIndexKey(), $id);
    }

    /**
     * Remove ID from index set
     * @param mixed $id
     * @return void
     */
    private function removeFromIndexSet(mixed $id): void
    {
        Redis::connection($this->connection)->srem($this->getIndexKey(), $id);
    }

    /**
     * Get all IDs in the table
     * @return array
     */
    private function getAllIds(): array
    {
        return Redis::connection($this->connection)->smembers($this->getIndexKey()) ?: [];
    }

    /**
     * Generate a unique ID
     * @return string
     */
    private function generateId(): string
    {
        return (string) Redis::connection($this->connection)->incr("{$this->table}:id_counter");
    }

    /**
     * Check if record matches query conditions
     * @param array $record
     * @return bool
     */
    private function matchesConditions(array $record): bool
    {
        foreach ($this->query_conditions as $key => $value) {
            if (!isset($record[$key]) || $record[$key] != $value) {
                return false;
            }
        }
        return true;
    }

    /**
     * Reset query conditions
     * @return void
     */
    private function resetQuery(): void
    {
        $this->query_conditions = [];
        $this->query_keys = [];
        $this->single = false;
    }
}
