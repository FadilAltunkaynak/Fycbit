<?php

namespace Modules\DemoTrade\Entities;

use Illuminate\Database\Eloquent\Model;

class FiveMinute extends Model
{
    protected $connection = 'DemoTradeMysql';
    protected $table = 'tv_chart_5mins';
    protected $fillable = ['interval', 'trade_coin_id', 'base_coin_id', 'open', 'close', 'high', 'low','volume'];

}
