<?php

namespace Modules\DemoTrade\Entities;

use Illuminate\Database\Eloquent\Model;

class SelectedCoinPair extends Model
{
    protected $connection = "DemoTradeMysql";
    protected $fillable = ['coin_pair_id', 'user_id', 'base_coin_id', 'trade_coin_id'];
}
