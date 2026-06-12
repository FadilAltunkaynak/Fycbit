<?php

namespace Modules\P2P\Entities;

use App\User;
use Modules\P2P\Casts\TimeCast;
use Modules\P2P\Casts\ArrayCast;
use Modules\P2P\Entities\PCoinSetting;
use Illuminate\Database\Eloquent\Model;

class PBuy extends Model
{
    protected $fillable = [
        'user_id',
        'uid',
        'coin_type',
        'currency',
        'price_type',
        'price',
        'price_rate',
        'amount',
        'claimed',
        'available',
        'minimum_trade_size',
        'maximum_trade_size',
        'terms',
        'auto_reply',
        'register_days',
        'coin_holding',
        'country',
        'status',
        'ip',
        'payment_times',
        'wallet_id',
        'rate_percentage',
        'coin_id',
        'payment_method',
        'admin_payment_method'
    ];

    protected $casts = [
        // 'payment_method' => ArrayCast::class,
        // 'country' => ArrayCast::class,
        // 'payment_times' => TimeCast::class,
    ];

    public function user(){
        return $this->hasOne(User::class,'id','user_id');
    }
    public function coin(){
        return $this->hasOne(PCoinSetting::class,'coin_type','coin_type');
    }

    public function getAttribute($key)
    {
        if(!$key) return;
        if($key == "price") return $this->retriveOriginalPrice();
        if(array_key_exists($key, $this->attributes) ||
            array_key_exists($key, $this->casts) ||
            $this->hasGetMutator($key) ||
            $this->hasAttributeMutator($key) ||
            $this->isClassCastable($key)) {
            return $this->getAttributeValue($key);
        }
        if (method_exists(self::class, $key)) {
            return;
        }
        return $this->getRelationValue($key);
    }

    private function retriveOriginalPrice()
    {
        $price = $this->getAttributeValue('price');
        if($this->price_type == TRADE_PRICE_FLOAT_TYPE){
            $market_price = convert_currency(1, $this->currency, $this->coin_type);
            $rate = 100;
            if($this->price_rate < 100){
                $rate = 100 - $this->price_rate;
                $result = ($market_price - (($market_price * $rate)/100));
                $price =  $result;
            }
            if($this->price_rate > 100) {
                $rate = $this->price_rate - 100;
                $result = ($market_price + (($market_price * $rate)/100));
                $price = $result;
            }
        }
        return $price;
    }
}
