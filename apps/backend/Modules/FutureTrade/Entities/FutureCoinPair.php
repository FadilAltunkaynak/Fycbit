<?php

namespace Modules\FutureTrade\Entities;

use App\Model\Coin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\FutureTrade\Casts\NumberTrim;
use Modules\FutureTrade\Emum\CollateralTypeEnum;
use Modules\FutureTrade\Emum\FutureCoinPairStatusEnum;
use Modules\FutureTrade\Entities\FutureBotSetting;

class FutureCoinPair extends Model
{
    public string|float $mark_price = 0;
    protected $fillable = [
        'uid',
        'code',
        'collateral_type',
        'margin_mode',
        'base_coin_id',
        'trade_coin_id',
        'base_coin_code',
        'trade_coin_code',
        'base_decimal',
        'trade_decimal',
        'maker_fees_percent',
        'taker_fees_percent',
        'min_amount',
        'max_amount',
        'min_stop_limit_percent',
        'max_stop_limit_percent',
        'floor_ratio',
        'cap_ratio',
        'leverage',
        'max_leverage',
        'max_open_orders',
        'funding_rate',
        'funding_next_time',
        'status',
        'slippage_percent',
        'is_chart_updated',
    ];

    protected $casts = [
        'status' => FutureCoinPairStatusEnum::class,
        'collateral_type' => CollateralTypeEnum::class,

        'cap_ratio' => NumberTrim::class,
        'maker_fees_percent' => NumberTrim::class,
        'taker_fees_percent' => NumberTrim::class,
        'min_amount' => NumberTrim::class,
        'max_amount' => NumberTrim::class,
        'floor_ratio' => NumberTrim::class,
        'leverage' => NumberTrim::class,
        'max_leverage' => NumberTrim::class,
        'max_open_orders' => NumberTrim::class,
        'funding_rate' => NumberTrim::class,
        'min_stop_limit_percent' => NumberTrim::class,
        'max_stop_limit_percent' => NumberTrim::class,
    ];

    public function baseCoin(): BelongsTo
    {
        return $this->belongsTo(Coin::class, 'base_coin_id');
    }

    public function tradeCoin(): BelongsTo
    {
        return $this->belongsTo(Coin::class, 'trade_coin_id');
    }

    public function price(): HasOne
    {
        return $this->hasOne(FuturePrice::class, 'future_coin_pair_id');
    }

    public function botSetting(): HasOne
    {
        return $this->hasOne(FutureBotSetting::class, 'future_coin_pair_id');
    }

    public function scopeStatusActive(Builder $query): Builder
    {
        return $query->where('status', FutureCoinPairStatusEnum::ACTIVE->value);
    }
}
