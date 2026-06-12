<?php

namespace Modules\P2P\Entities;

use Illuminate\Database\Eloquent\Model;

class PCurrencySetting extends Model
{
    protected $fillable = ['currency_code', 'trade_status', 'maximum_price','minimum_price'];
}
