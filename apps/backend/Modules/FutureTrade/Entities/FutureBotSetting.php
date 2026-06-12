<?php

namespace Modules\FutureTrade\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\FutureTrade\Casts\NumberTrim;

class FutureBotSetting extends Model
{
    protected $fillable = [
        'uid',
        'future_coin_pair_id',
        'amount_low',
        'amount_high',
        'price_low',
        'price_high',
        'order_interval',
        'status',
    ];

    protected $casts = [
        'amount_low' => NumberTrim::class,
        'amount_high' => NumberTrim::class,
        'price_low' => NumberTrim::class,
        'price_high' => NumberTrim::class,
    ];
}
