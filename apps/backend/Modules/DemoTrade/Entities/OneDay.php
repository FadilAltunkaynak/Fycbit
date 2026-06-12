<?php

namespace Modules\DemoTrade\Entities;

use Illuminate\Database\Eloquent\Model;

class OneDay extends Model
{
    protected $connection = 'DemoTradeMysql';
    protected $table = 'tv_chart_1days';
    protected $fillable = ['interval', 'trade_coin_id', 'base_coin_id', 'open', 'close', 'high', 'low','volume'];

}
