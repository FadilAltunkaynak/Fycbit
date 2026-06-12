<?php

namespace Modules\P2P\Entities;

use Illuminate\Database\Eloquent\Model;

class PCoinSetting extends Model
{
    protected $fillable = ['coin_type', 'trade_status', 'maximum_price', 'minimum_price', 'sell_fees', 'buy_fees'];
}
