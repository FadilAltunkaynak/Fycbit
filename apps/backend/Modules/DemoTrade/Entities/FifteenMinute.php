<?php

namespace Modules\DemoTrade\Entities;

use Illuminate\Database\Eloquent\Model;

class FifteenMinute extends Model
{
    protected $connection = 'DemoTradeMysql';
    protected $table = 'tv_chart_15mins';
    protected $fillable = ['interval', 'trade_coin_id', 'base_coin_id', 'open', 'close', 'high', 'low','volume'];

}
