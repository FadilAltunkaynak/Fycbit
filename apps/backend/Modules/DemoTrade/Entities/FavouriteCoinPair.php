<?php

namespace Modules\DemoTrade\Entities;

use Illuminate\Database\Eloquent\Model;

class FavouriteCoinPair extends Model
{
    protected $connection = "DemoTradeMysql";
    protected $fillable = ['coin_pairs_id', 'user_id'];
}
