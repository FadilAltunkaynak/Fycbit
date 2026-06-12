<?php

namespace App\Http\Services;

use App\Http\Repositories\TradingViewChartRepository;
use App\Model\FifteenMinute;
use App\Model\FiveMinute;
use App\Model\FourHour;
use App\Model\OneDay;
use App\Model\ThirtyMinute;
use App\Model\TwoHour;
use App\Services\CacheService\SpotChartCacheService;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class TradingViewChartService
{
    const INTERVAL_TO_MODEL_MAPPING = [
        '5' => FiveMinute::class,
        '15' => FifteenMinute::class,
        '30' => ThirtyMinute::class,
        '120' => TwoHour::class,
        '240' => FourHour::class,
        '1440' => OneDay::class,
    ];

    private TradingViewChartRepository $repository;
    private SpotChartCacheService $spotChartCacheService;

    public function __construct()
    {
        $this->repository = app()->make(TradingViewChartRepository::class);
        $this->spotChartCacheService = app()->make(SpotChartCacheService::class);
    }

    public function getChartData(int $startTime, int $endTime, int $interval, int $baseCoinId, int $tradeCoinId, ?int $trade = null)
    {
        return $this->repository->getChartData(
            $this->getModel($interval),
            $baseCoinId,
            $tradeCoinId,
            $startTime,
            $endTime,
            $trade
        );
    }

    private function getModel(int $interval)
    {
        if (! array_key_exists($interval, self::INTERVAL_TO_MODEL_MAPPING)) {
            throw new InvalidArgumentException("Invalid interval: {$interval}");
        }

        return app()->make(self::INTERVAL_TO_MODEL_MAPPING[$interval]);
    }

    public function updateCandleData($transaction)
    {
        $price = $transaction->price;
        $volume = $transaction->total;
        $baseCoinId = $transaction->base_coin_id;
        $tradeCoinId = $transaction->trade_coin_id;

        $transactionTime = strtotime($transaction->created_at);

        foreach (self::INTERVAL_TO_MODEL_MAPPING as $interval => $model) {
            $interval = $transactionTime - ($transactionTime % ($interval * 60));
            $this->insertCandle(
                app()->make($model),
                $price,
                $volume,
                $baseCoinId,
                $tradeCoinId,
                $interval
            );
        }
    }

    public function insertCandle(Model $model, $price, $volume, int $baseCoinId, int $tradeCoinId, int $intervalTime)
    {
        $name = $model->getTable();
        $lastCandle = $this->getLastCandle($model, $baseCoinId, $tradeCoinId);
        $price = (float) $price;
        $volume = (float) $volume;

        if (! is_null($lastCandle) && (int) $lastCandle['interval'] === $intervalTime) {
            $close = $price;
            $high = max((float) $lastCandle['high'], $price);
            $low = min((float) $lastCandle['low'], $price);
            $updatedVolume = (float) $lastCandle['volume'] + $volume;

            $this->repository->updateCandle(
                $model,
                ['id' => (int) $lastCandle['id']],
                ['close' => $close, 'high' => $high, 'low' => $low, 'volume' => $updatedVolume]
            );

            $lastCandle['close'] = $close;
            $lastCandle['high'] = $high;
            $lastCandle['low'] = $low;
            $lastCandle['volume'] = $updatedVolume;
            $lastCandle['base_coin_id'] = $baseCoinId;
            $lastCandle['trade_coin_id'] = $tradeCoinId;
            $this->spotChartCacheService->setLastCandle($model, $baseCoinId, $tradeCoinId, $lastCandle);

            return;
        }

        $open = is_null($lastCandle) ? $price : (float) $lastCandle['close'];
        $close = $price;
        $high = max($price, $open);
        $low = min($price, $open);
        $data = [
            'base_coin_id' => $baseCoinId,
            'trade_coin_id' => $tradeCoinId,
            'interval' => $intervalTime,
            'open' => $open,
            'volume' => $volume,
            'close' => $close,
            'high' => $high,
            'low' => $low,
        ];

        $newCandle = $this->repository->createNewCandle($model, $data);
        $this->spotChartCacheService->setLastCandle($model, $baseCoinId, $tradeCoinId, [
            'id' => $newCandle->id,
            'base_coin_id' => $newCandle->base_coin_id,
            'trade_coin_id' => $newCandle->trade_coin_id,
            'interval' => $newCandle->interval,
            'open' => $newCandle->open,
            'close' => $newCandle->close,
            'high' => $newCandle->high,
            'low' => $newCandle->low,
            'volume' => $newCandle->volume,
        ]);
    }

    private function getLastCandle(Model $model, int $baseCoinId, int $tradeCoinId): ?array
    {
        $cachedCandle = $this->spotChartCacheService->getLastCandle($model, $baseCoinId, $tradeCoinId);
        if (! is_null($cachedCandle)) {
            return $cachedCandle;
        }

        $lastCandle = $model->where([
            'base_coin_id' => $baseCoinId,
            'trade_coin_id' => $tradeCoinId,
        ])->orderBy('interval', 'DESC')->first();

        if (is_null($lastCandle)) {
            return null;
        }

        $normalized = [
            'id' => $lastCandle->id,
            'base_coin_id' => $lastCandle->base_coin_id,
            'trade_coin_id' => $lastCandle->trade_coin_id,
            'interval' => $lastCandle->interval,
            'open' => $lastCandle->open,
            'close' => $lastCandle->close,
            'high' => $lastCandle->high,
            'low' => $lastCandle->low,
            'volume' => $lastCandle->volume,
        ];
        $this->spotChartCacheService->setLastCandle($model, $baseCoinId, $tradeCoinId, $normalized);

        return $normalized;
    }
}
