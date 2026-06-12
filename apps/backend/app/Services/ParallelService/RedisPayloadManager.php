<?php

namespace App\Services\ParallelService;

use Illuminate\Support\Facades\Redis;

trait RedisPayloadManager
{
    private function ok(\Predis\Response\Status $response)
    {
        $payload = $response->getPayload();
        return $payload == "OK";
    }
    public function setRedisPayload(string $key, array $value): bool
    {
        return $this->ok(Redis::connection('parallel_cache')->hmset($key, $value));
    }

    public function hasProperty(string $key, string $valueKey): mixed
    {
        return Redis::connection('parallel_cache')->hexists($key, $valueKey);
    }

    public function getPayloadProperty(string $key, string $valueKey): mixed
    {
        return Redis::connection('parallel_cache')->hget($key, $valueKey);
    }

    public function setPayloadProperty(string $key, string $valueKey, mixed $value): mixed
    {
        return Redis::connection('parallel_cache')->hset($key, $valueKey, $value);
    }

    public function getPayloadPropertyForget(string $key, string $valueKey): mixed
    {
        return Redis::connection('parallel_cache')->hget($key, $valueKey);
    }

    public function forgetPayloadProperty(string $key, array $valueKey): mixed
    {
        return Redis::connection('parallel_cache')->del($key, $valueKey);
    }
    public function forgetPayload(string $key): bool
    {
        return Redis::connection('parallel_cache')->del($key);
    }

    public function getPayload(string $key) : array
    {
        return Redis::connection('parallel_cache')->hgetall($key);
    }

    public function addProcessIdSet(int $id): bool
    {
        return Redis::connection('parallel_cache')->sadd('parallel_process_fire_ids', $id);
    }

    public function getProcessIdSet(): array
    {
        return (array) Redis::connection('parallel_cache')->sMembers('parallel_process_fire_ids');
    }

    public function hasProcessIdSet(int $id): bool
    {
        return Redis::connection('parallel_cache')->sIsMember('parallel_process_fire_ids', $id);
    }

    public function removeProcessIdSet(int $id): bool
    {
        return Redis::connection('parallel_cache')->srem('parallel_process_fire_ids', $id);
    }

    public function addChildProcessResult(int|string $id ,string $result_data): bool
    {
        return Redis::connection('parallel_cache')->sadd("child_process_result_$id", $result_data);
    }

    public function getChildProcessResult(int|string $id ): array
    {
        $result = (array) Redis::connection('parallel_cache')->sMembers("child_process_result_$id");
        Redis::connection('parallel_cache')->del("child_process_result_$id");
        return $result;
    }

}