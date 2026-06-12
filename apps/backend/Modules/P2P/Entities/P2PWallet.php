<?php

namespace Modules\P2P\Entities;

use Illuminate\Database\Eloquent\Model;

class P2PWallet extends Model
{
    const WALLET_BALANCE_TRANSFER_SEND = 1;
    const WALLET_BALANCE_TRANSFER_RECEIVE = 2;

    protected $table = "p2p_wallets";
    protected $fillable = [
        'user_id',
        'name',
        'balance',
        'referral_balance',
        'status',
        'is_primary',
        'coin_type',
        'coin_id',
        'key',
        'type'
    ];
    
}
