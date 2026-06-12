<?php

namespace Modules\FutureTrade\Entities;

use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\FutureTrade\Emum\MarginModeEnum;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Emum\PositionStatusEnum;

class FuturePosition extends Model
{
    public ?FutureCoinPair $coin_pair = null;
    protected $fillable = [
        "uid",
        "future_coin_pair_id",
        "future_coin_pair_uid",
        "base_coin_id",
        "trade_coin_id",
        "future_wallet_id",
        "user_id",
        "order_type",
        "price",
        "amount",
        "tp_price",
        "sl_price",
        "margin_balance",
        "margin_mode",
        "leverage",
        "status",
    ];

    protected $casts = [
        'order_type' => OrderType::class,
        'margin_mode' => MarginModeEnum::class,
        'status' => PositionStatusEnum::class
    ];

    public function leverageSetting(): BelongsTo
    {
        return $this->belongsTo(FutureLeverageSetting::class, 'future_coin_pair_uid', 'coin_pair_uid');
    }

    public function coinPair(): BelongsTo
    {
        return $this->belongsTo(FutureCoinPair::class, 'future_coin_pair_id');
    }

    public function future_wallet(): BelongsTo
    {
        return $this->belongsTo(FutureWallet::class, 'future_wallet_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeGetLeverageSetting(Builder $builder): Builder
    {
        return $builder->join('future_leverage_settings as fls', function ($join) {
            $join->on('future_positions.future_coin_pair_uid', '=', 'fls.coin_pair_uid')
                ->whereRaw('(future_positions.price * ABS(future_positions.amount)) 
                        BETWEEN fls.min_position_amount 
                        AND fls.max_position_amount');
        });
    }
}
