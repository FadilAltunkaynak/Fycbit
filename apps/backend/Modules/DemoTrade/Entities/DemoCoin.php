<?php

namespace Modules\DemoTrade\Entities;

use Illuminate\Database\Eloquent\Model;

class DemoCoin extends Model
{
    protected $connection = 'DemoTradeMysql';
    protected $fillable = [ 'coin_id', 'coin_type', 'trade_status', 'faucet_amount','faucet_amount', 'faucet_min_balance' ];
}
