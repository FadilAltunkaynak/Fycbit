<?php

namespace App\Model;

use App\Model\CoinNetwork;
use App\Model\CoinSetting;
use App\Model\HasAnyFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coin extends Model
{
    use HasAnyFactory;
    protected $fillable = [
        'name',
        'coin_type',
        'currency_type',
        'currency_id',
        'active_provider',
        'network',
        'decimal',
        'status',
        'is_withdrawal',
        'is_deposit',
        'is_buy',
        'is_sell',
        'coin_icon',
        'is_base',
        'is_currency',
        'is_primary',
        'is_wallet',
        'is_demo_trade',
        'is_transferable',
        'is_virtual_amount',
        'trade_status',
        'sign',
        'minimum_buy_amount',
        'minimum_sell_amount',
        'minimum_withdrawal',
        'maximum_withdrawal',
        'maximum_buy_amount',
        'maximum_sell_amount',
        'max_send_limit',
        'withdrawal_fees',
        'withdrawal_fees_type',
        'coin_price',
        'admin_approval',
        'ico_id',
        'is_listed',
        'last_block_number',
        'last_timestamp',
        "sync_rate_status",
        "convert_status",
        "min_convert_amount",
        "max_convert_amount",
        "convert_fee_type",
        "convert_fee",
        "market_cap",
        'to_block_number',
        'from_block_number',
    ];

    public function setCoinTypeAttribute($value)
    {
        $this->attributes['coin_type'] = strtoupper($value);
    }

    public function coin_pair_usdt()
    {
        return $this->belongsTo(CoinPair::class, 'id', 'child_coin_id');
    }

    public function coin_network()
    {
        return $this->hasMany(CoinNetwork::class, 'currency_id');
    }

    public function coin_setting()
    {
        return $this->belongsTo(CoinSetting::class, 'coin_id');
    }
}
