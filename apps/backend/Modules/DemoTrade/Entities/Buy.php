<?php

namespace Modules\DemoTrade\Entities;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Modules\DemoTrade\Events\OrderHasPlaced;
use Illuminate\Database\Eloquent\SoftDeletes;

class Buy extends Model
{
    use SoftDeletes;
    protected $connection = 'DemoTradeMysql';
    

    protected $dates = ['deleted_at'];

    protected $fillable = ['user_id', 'condition_buy_id', 'trade_coin_id', 'base_coin_id', 'amount', 'processed',
        'virtual_amount', 'price', 'btc_rate', 'status', 'is_market', 'category','request_amount','processed_request_amount',
        'maker_fees', 'taker_fees', 'is_conditioned',
        'is_bot','margin_mode','leverage','take_profit','stop_loss','is_position',
        'liquidation_price','future_trade_time','future_trade_type'
    ];

    public $dispatchesEvents = [
        'created' => OrderHasPlaced::class
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function baseCoin(){
        return $this->hasOne(Coin::class, 'id', 'base_coin_id');
    }

    public function tradeCoin(){
        return $this->hasOne(Coin::class, 'id', 'trade_coin_id');
    }
    public static function highestBid($baseCoinId, $tradeCoinId){
        return Buy::select(DB::connection('DemoTradeMysql')->raw('Coalesce(TRUNCATE(max(price),8),0) as price'))
            ->where(['base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId,'is_market' => 0,'status' => 0])->first()->price;
    }
    public static function quoteVolume($baseCoinId, $tradeCoinId){
        return Buy::select(DB::connection('DemoTradeMysql')->raw('TRUNCATE(Coalesce(Sum(( amount - processed ) * price), 0),8) volume'))
            ->where(['base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId,'is_market' => 0,'status' => 0])->first()->volume;
    }
}
