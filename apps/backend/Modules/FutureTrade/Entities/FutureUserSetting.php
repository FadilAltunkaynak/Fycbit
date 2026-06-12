<?php

namespace Modules\FutureTrade\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\FutureTrade\Emum\MarginModeEnum;

class FutureUserSetting extends Model
{
    protected $fillable = [
        'uid',
        'user_id',
        'coin_pair_id',
        'leverage',
        'margin_mode',
    ];

    protected $casts = [
        'margin_mode' => MarginModeEnum::class,
    ];
}
