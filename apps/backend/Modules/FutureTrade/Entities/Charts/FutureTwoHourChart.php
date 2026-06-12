<?php

namespace Modules\FutureTrade\Entities\Charts;

use Illuminate\Database\Eloquent\Model;

class FutureTwoHourChart extends Model
{
    protected $fillable = ['interval', 'trade_coin_id', 'base_coin_id', 'open', 'close', 'high', 'low','volume'];
}
