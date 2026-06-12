<?php

namespace Modules\DemoTrade\Entities;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;
    protected $connection = 'DemoTradeMysql';

    protected $fillable = ['transaction_id', 'base_coin_id', 'trade_coin_id', 'buy_id', 'sell_id', 'buy_user_id', 'sell_user_id', 'amount',
        'price', 'last_price','price_order_type', 'btc_rate', 'btc', 'total', 'buy_fees', 'sell_fees', 'remove_from_chart'
    ];

    public static function baseVolume24($baseCoinId){
        return Transaction::select(DB::connection("DemoTradeMysql")->raw('TRUNCATE(Coalesce(Sum(amount), 0),8) volume'))
            ->where(['base_coin_id' => $baseCoinId])
            ->where('created_at', '>', Carbon::now()->subDays(1))
            ->first()->volume;
    }
    public static function quoteVolume24($tradeCoinId){
        return Transaction::select(DB::connection("DemoTradeMysql")->raw('TRUNCATE(Coalesce(Sum(amount), 0),8) volume'))
            ->where('created_at', '>', Carbon::now()->subDays(1))
            ->where(['trade_coin_id' => $tradeCoinId])->first()->volume;
    }
}
