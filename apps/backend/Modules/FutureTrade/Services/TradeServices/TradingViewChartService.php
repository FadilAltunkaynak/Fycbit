<?php

namespace Modules\FutureTrade\Services\TradeServices;

use App\Facades\ResponseFacade;
use App\Http\Repositories\TradingViewChartRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Modules\FutureTrade\Emum\ChartIntervalEnum;
use Modules\FutureTrade\Entities\FutureTrade;
use Modules\FutureTrade\Http\Requests\Api\FutureGetCandlesRequest;
use Modules\FutureTrade\Repositories\CoinPairRepository\CoinPairRepository as RepositoriesCoinPairRepository;

class TradingViewChartService
{
    private TradingViewChartRepository $repository;

    public function __construct()
    {
        $this->repository = app()->make(TradingViewChartRepository::class);
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
        $intervalEnum = ChartIntervalEnum::tryFrom($interval);
        if (! $intervalEnum) {
            ResponseFacade::failed(__("Invalid interval: {$interval}"))->throw();
        }

        return app()->make($intervalEnum->getModel());
    }

    public function updateCandleData(FutureTrade $transaction)
    {
        $price = $transaction->price;
        $volume = bcmulx($transaction->price, $transaction->amount, $transaction->coinPair->trade_decimal ?: 8);
        $baseCoinId = $transaction->base_coin_id;
        $tradeCoinId = $transaction->trade_coin_id;

        $transactionTime = Carbon::parse($transaction->created_at);

        foreach (ChartIntervalEnum::cases() as $intervalEnum) {
            $interval = $transactionTime->copy()
                ->minute(
                    floor($transactionTime->minute / $intervalEnum->value) * $intervalEnum->value
                )->second(0);

            $this->insertCandle(
                app()->make($intervalEnum->getModel()),
                $price,
                $volume,
                $baseCoinId,
                $tradeCoinId,
                $interval->timestamp
            );
        }
    }

    public function insertCandle(Model $model, $price, $volume, int $baseCoinId, int $tradeCoinId, int $intervalTime)
    {
        $candle = $this->repository->getCandle($model,
            ['base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId, 'interval' => ['>=' ,$intervalTime]]
        )->first();

        $lastCandle = $model->where(['base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId])
            ->orderBy('interval', 'DESC')->first();

        if (is_null($candle)) {
            $open = is_null($lastCandle) ? $price : $lastCandle->close;
            $close = $price;
            $high = $price > $open ? $price : $open;
            $low = $price < $open ? $price : $open;
            $data = [
                'base_coin_id' => $baseCoinId,
                'trade_coin_id' => $tradeCoinId,
                'interval' => $intervalTime, 'open' => $open,
                'volume' => $volume,
                'close' => $close,
                'high' => $high,
                'low' => $low,
            ];

            $this->repository->createNewCandle($model, $data);

            return;
        }

        $close = $price;
        $high = $candle->high < $price ? $price : $candle->high;
        $low = $candle->low > $price ? $price : $candle->low;
        $volume = $candle->volume + $volume;
        $this->repository->updateCandle($model,
            ['id' => $candle->id],
            ['close' => $close, 'high' => $high, 'low' => $low, 'volume' => $volume]
        );
    }

    public function getCandles(FutureGetCandlesRequest $request)
    {
        $coinPair = RepositoriesCoinPairRepository::getCoinPairByUid($request->coin_pair_uid);

        if(! $coinPair){
            ResponseFacade::failed(__("Coin pair not found"))->throw();
        }

        $startTime = $request->input('from', now()->subDays(10)->timestamp);
        $endTime = $request->input('to', now()->timestamp);

        if($startTime >= $endTime){
            ResponseFacade::failed(__("From time is always big than to time"))->throw();
        }

        return $this->getChartData(
            startTime: $startTime,
            endTime: $endTime,
            interval: $request->interval,
            baseCoinId: $coinPair->base_coin_id,
            tradeCoinId: $coinPair->trade_coin_id
        );
    }
}
