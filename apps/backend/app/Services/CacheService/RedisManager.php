<?php

namespace App\Services\CacheService;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;

abstract class RedisManager
{
    private string $connectionName = 'spot_trade_cache';
    private string $connectionStoreName = 'spot_trade_cache_store';
    protected function cache(): Repository
    {
        return cache()->store($this->connectionStoreName);
    }
    protected function redis(): Connection
    {
        return Redis::connection($this->connectionName);
    }

    protected function dbFlush(): mixed
    {
        return $this->redis()->flushdb();
    }

    private function ok(\Predis\Response\Status $response)
    {
        $payload = $response->getPayload();
        return $payload == "OK";
    }
    public function setRedisPayload(string $key, array $value): bool
    {
        return $this->ok($this->redis()->hmset($key, $value));
    }

    public function hasProperty(string $key, string $valueKey): mixed
    {
        return $this->redis()->hexists($key, $valueKey);
    }

    public function getPayloadProperty(string $key, string $valueKey): mixed
    {
        return $this->redis()->hget($key, $valueKey);
    }

    public function setPayloadProperty(string $key, string $valueKey, mixed $value): mixed
    {
        return $this->redis()->hset($key, $valueKey, $value);
    }

    public function getPayloadPropertyForget(string $key, string $valueKey): mixed
    {
        return $this->redis()->hget($key, $valueKey);
    }

    public function forgetPayloadProperty(string $key, array $valueKey): mixed
    {
        return $this->redis()->del($key, $valueKey);
    }
    public function forgetPayload(string $key): bool
    {
        return $this->redis()->del($key);
    }

    public function getPayload(string $key) : array
    {
        return $this->redis()->hgetall($key);
    }

    public function addProcessIdSet(int $id): bool
    {
        return $this->redis()->sadd('parallel_process_fire_ids', $id);
    }

    public function getProcessIdSet(): array
    {
        return (array) $this->redis()->sMembers('parallel_process_fire_ids');
    }

    public function hasProcessIdSet(int $id): bool
    {
        return $this->redis()->sIsMember('parallel_process_fire_ids', $id);
    }

    public function removeProcessIdSet(int $id): bool
    {
        return $this->redis()->srem('parallel_process_fire_ids', $id);
    }

    public function addChildProcessResult(int|string $id ,string $result_data): bool
    {
        return $this->redis()->sadd("child_process_result_$id", $result_data);
    }

    public function getChildProcessResult(int|string $id ): array
    {
        $result = (array) $this->redis()->sMembers("child_process_result_$id");
        Redis::connection('parallel_cache')->del("child_process_result_$id");
        return $result;
    }

}