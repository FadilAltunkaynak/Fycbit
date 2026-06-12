<?php

namespace Modules\DemoTrade\Entities;

use Illuminate\Database\Eloquent\Model;

class FaucetHistory extends Model
{
    protected $connection = "DemoTradeMysql";
    protected $guarded = [];
}
