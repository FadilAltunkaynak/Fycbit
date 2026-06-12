<?php

namespace Modules\DemoTrade\Entities;

use Illuminate\Database\Eloquent\Model;

class FavouriteOrderBook extends Model
{
    protected $connection = "DemoTradeMysql";
    protected $fillable = ['user_id', 'base_coin_id','trade_coin_id','price','type'];
}
