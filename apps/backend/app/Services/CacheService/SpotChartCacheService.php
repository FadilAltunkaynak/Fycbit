<?php

namespace App\Services\CacheService;

use App\Model\OneDay;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

final class SpotChartCacheService extends RedisManager
{
    private const ORDERBOOK_READY_TTL_SECONDS = 1800;
    public function __construct(){}

    protected function lock(): mixed
    {
        return $this->redis()->set('chart:load:lock', 1, 'NX', 'EX', 300);
    }

    protected function unlock(): mixed
    {
        return $this->redis()->del('chart:load:lock');
    }

    protected function isLocked(): mixed
    {
        return $this->redis()->exists('chart:load:lock');
    }


    public function addChatLastPrice($tradeCoinId, $baseCoinId): mixed
    {
        $yesterday  = Carbon::yesterday();

        $price24AgoPrice = OneDay::select("close")->where([
            'base_coin_id'  => $baseCoinId,
            'trade_coin_id' => $tradeCoinId
        ])->whereDate("created_at", $yesterday->format("Y-m-d"))->latest()->first()?->close;

        if(!$price24AgoPrice) $price24AgoPrice = 0;

        $this->cache()->put("chart:1d:{$tradeCoinId}_{$baseCoinId}", $price24AgoPrice, self::ORDERBOOK_READY_TTL_SECONDS);

        return $price24AgoPrice;
    }

    public function getLast24ClosePrice($tradeCoinId, $baseCoinId): mixed
    {
        $key = "chart:1d:{$tradeCoinId}_{$baseCoinId}";

        $price = null;
        if(! $this->cache()->has($key)) {
            $price = $this->addChatLastPrice($tradeCoinId, $baseCoinId);
        }
        $price ??= $this->cache()->get($key);
        return $price ?: 0;
    }

    public function getLastCandle(Model $model, int $baseCoinId, int $tradeCoinId): ?array
    {
        [$zsetKey, $hashPrefix] = $this->lastCandleCacheKeys($model, $baseCoinId, $tradeCoinId);
        $lastIds = $this->redis()->zrevrange($zsetKey, 0, 0);
        if (empty($lastIds)) {
            return null;
        }

        $id = $lastIds[0];
        $data = $this->redis()->hgetall($hashPrefix . ':' . $id);
        if (empty($data)) {
            $this->redis()->zrem($zsetKey, $id);

            return null;
        }

        return [
            'id' => (int) ($data['id'] ?? $id),
            'base_coin_id' => (int) ($data['base_coin_id'] ?? $baseCoinId),
            'trade_coin_id' => (int) ($data['trade_coin_id'] ?? $tradeCoinId),
            'interval' => (int) ($data['interval'] ?? 0),
            'open' => (float) ($data['open'] ?? 0),
            'close' => (float) ($data['close'] ?? 0),
            'high' => (float) ($data['high'] ?? 0),
            'low' => (float) ($data['low'] ?? 0),
            'volume' => (float) ($data['volume'] ?? 0),
        ];
    }

    public function setLastCandle(Model $model, int $baseCoinId, int $tradeCoinId, array $candle): void
    {
        [$zsetKey, $hashPrefix] = $this->lastCandleCacheKeys($model, $baseCoinId, $tradeCoinId);
        $oldIds = $this->redis()->zrange($zsetKey, 0, -1);

        $this->redis()->zadd($zsetKey, (int) $candle['interval'], (string) $candle['id']);
        $this->redis()->zremrangebyrank($zsetKey, 0, -2);

        $hashKey = $hashPrefix . ':' . $candle['id'];
        $this->redis()->hmset($hashKey, [
            'id' => (int) $candle['id'],
            'base_coin_id' => (int) $candle['base_coin_id'],
            'trade_coin_id' => (int) $candle['trade_coin_id'],
            'interval' => (int) $candle['interval'],
            'open' => (string) $candle['open'],
            'close' => (string) $candle['close'],
            'high' => (string) $candle['high'],
            'low' => (string) $candle['low'],
            'volume' => (string) $candle['volume'],
        ]);

        foreach ($oldIds as $oldId) {
            if ((string) $oldId !== (string) $candle['id']) {
                $this->redis()->del($hashPrefix . ':' . $oldId);
            }
        }
    }

    private function lastCandleCacheKeys(Model $model, int $baseCoinId, int $tradeCoinId): array
    {
        $table = $model->getTable();
        $pairKey = $baseCoinId . ':' . $tradeCoinId;
        $keyPrefix = "tv_chart:last_candle:{$table}:{$pairKey}";

        return [$keyPrefix . ':zset', $keyPrefix . ':hash'];
    }
}
