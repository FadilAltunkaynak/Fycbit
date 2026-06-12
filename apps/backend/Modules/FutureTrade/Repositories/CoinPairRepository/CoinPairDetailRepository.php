<?php

namespace Modules\FutureTrade\Repositories\CoinPairRepository;

use Illuminate\Support\Collection;
use Modules\FutureTrade\Entities\FutureCoinPair;

class CoinPairDetailRepository
{
    /**
     * Get Coin Pair By Coin Code
     * Supports both single code (BTCUSDT)
     */
    public function getCoinPairByCode(string $code): ?FutureCoinPair
    {
        return FutureCoinPair::where('code', $code)->first();
    }

    /**
     * Get Coin Pair By Trade Coin Code
     */
    public function getCoinPairByTradeCoinCode(string $tradeCoinCode): Collection
    {
        return FutureCoinPair::where('trade_coin_code', strtoupper($tradeCoinCode))
            ->where('status', 1)
            ->get();
    }

    /**
     * Get All Active Coin Pairs
     */
    public function getAllActiveCoinPairs(): Collection
    {
        return FutureCoinPair::where('status', 1)
            ->select(
                'id', 
                'uid', 
                'code', 
                'base_coin_code', 
                'trade_coin_code', 
                'base_coin_id',
                'trade_coin_id',
                'base_decimal', 
                'trade_decimal',
                'status',
                'min_amount',
                'max_amount',
                'maker_fees_percent',
                'taker_fees_percent'
            )
            ->get();
    }

    /**
     * Get Coin Pair Details with Formatted Data
     */
    public function getCoinPairWithDetails(string $code): ?array
    {
        $coinPair = $this->getCoinPairByCode($code);

        if (!$coinPair) {
            return null;
        }

        return [
            'id' => $coinPair->id,
            'uid' => $coinPair->uid,
            'code' => $coinPair->code,
            'base_coin_code' => $coinPair->base_coin_code,
            'trade_coin_code' => $coinPair->trade_coin_code,
            'base_coin_id' => $coinPair->base_coin_id,
            'trade_coin_id' => $coinPair->trade_coin_id,
            'base_decimal' => $coinPair->base_decimal,
            'trade_decimal' => $coinPair->trade_decimal,
            'status' => $coinPair->status?->value,
            'min_amount' => $coinPair->min_amount,
            'max_amount' => $coinPair->max_amount,
            'maker_fees_percent' => $coinPair->maker_fees_percent,
            'taker_fees_percent' => $coinPair->taker_fees_percent,
            'min_stop_limit_percent' => $coinPair->min_stop_limit_percent,
            'max_stop_limit_percent' => $coinPair->max_stop_limit_percent,
            'floor_ratio' => $coinPair->floor_ratio,
            'cap_ratio' => $coinPair->cap_ratio,
            'leverage' => $coinPair->leverage,
            'max_leverage' => $coinPair->max_leverage,
            'max_open_orders' => $coinPair->max_open_orders,
            'funding_rate' => $coinPair->funding_rate,
            'funding_next_time' => $coinPair->funding_next_time,
            'slippage_percent' => $coinPair->slippage_percent,
        ];
    }
}
