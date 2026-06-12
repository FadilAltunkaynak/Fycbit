<?php

namespace Modules\FutureTrade\Entities;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Entities\FutureBuy;
use Modules\FutureTrade\Entities\FutureCoinPair;
use Modules\FutureTrade\Entities\FutureSell;

class FutureTrade extends Model
{
    /**
     * Set Order Type From Outside
     */
    public OrderType $orderType;
    public ?FutureCoinPair $coinPair = null;
    protected $fillable = [
        "uid",
        "future_coin_pair_id",
        "base_coin_id",
        "trade_coin_id",
        "seller_id",
        "buyer_id",
        "taker_id",
        "maker_id",
        "buy_id",
        "sell_id",
        "order_type",
        "is_bot",
        "price",
        "amount",
        "total_price",
        "last_price",
        "taker_fees",
        "maker_fees",
        "buyer_realized_profit",
        "seller_realized_profit",
        "status",
    ];

    protected $casts = [
        'order_type' => OrderType::class,
        'is_bot' => 'integer',
        'created_at' => 'datetime:H:i:s',
    ];

    public function buyOrder(): BelongsTo
    {
        return $this->belongsTo(FutureBuy::class, 'buy_id');
    }

    public function sellOrder(): BelongsTo
    {
        return $this->belongsTo(FutureSell::class, 'sell_id');
    }
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
    public function coin_pair(): BelongsTo
    {
        return $this->belongsTo(FutureCoinPair::class, 'future_coin_pair_id');
    }
}
