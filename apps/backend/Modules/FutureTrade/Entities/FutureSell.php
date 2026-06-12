<?php

namespace Modules\FutureTrade\Entities;

use App\Model\Coin;
use Illuminate\Database\Eloquent\Model;
use Modules\FutureTrade\Casts\NumberTrim;
use Modules\FutureTrade\Emum\MarginModeEnum;
use Modules\FutureTrade\Emum\OrderMethod;
use Modules\FutureTrade\Emum\OrderStatusEnum;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Emum\TpSlType;
use Modules\FutureTrade\Entities\Traits\OrderTrait;

class FutureSell extends Model
{
    use OrderTrait;
    public ?FutureCoinPair $coin_pair = null;
    public ?Coin $trade_coin = null;
    public ?Coin $base_coin = null;
    public ?FutureWallet $wallet = null;
    protected $fillable = [
        'uid',
        'future_coin_pair_id',
        'base_coin_id',
        'trade_coin_id',
        'user_id',
        'margin_mode',
        'order_method',
        'is_reduce',
        'is_bot',
        'price',
        'amount',
        'total_price',
        'processed_amount',
        'pending_amount',
        'stop_price',
        'market_price',
        'mark_price',
        'index_price',
        'tp_price',
        'sl_price',
        'tpsl_type',
        'status'
    ];

    protected $casts = [
        'status' => OrderStatusEnum::class,
        'order_type' => OrderType::class,
        'price' => NumberTrim::class,
        'order_method' => OrderMethod::class,
        'is_bot' => 'integer',
        'pending_amount' => NumberTrim::class,
        'tpsl_type' => TpSlType::class,
        'margin_mode' => MarginModeEnum::class,
    ];
}
