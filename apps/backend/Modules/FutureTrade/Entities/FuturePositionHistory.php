<?php

namespace Modules\FutureTrade\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\FutureTrade\Emum\MarginModeEnum;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Emum\PositionStatusEnum;

class FuturePositionHistory extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'future_position_id',
        'margin_mode',
        'mark_price',
        'price',
        'pnl',
        'liquidation_price',
        'leverage',
        'maintenance_margin',
        'available_balance',
        'margin_ratio',
        'status',
        'margin_balance',
    ];

    protected $casts = [
        'order_type' => OrderType::class,
        'margin_mode' => MarginModeEnum::class,
        'status' => PositionStatusEnum::class
    ];
}
