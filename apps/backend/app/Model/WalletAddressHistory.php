<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class WalletAddressHistory extends Model
{
    protected $fillable = [
        'wallet_id',
        'address',
        'user_id',
        'network',
        'network_id',
        'base_type',
        'coin_type',
        'wallet_key',
        'public_key',
        'coin_id',
        'is_encrypted',
        'memo',
        'status',
        'coin_payment_wallet_id',
        'rented_till'
    ];

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'id', 'wallet_id');
    }
}
