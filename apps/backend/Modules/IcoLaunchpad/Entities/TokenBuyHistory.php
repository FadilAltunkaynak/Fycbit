<?php

namespace Modules\IcoLaunchpad\Entities;

use App\Model\Coin;
use App\Model\UserBank;
use App\Model\Wallet;
use App\Model\WalletAddressHistory;
use App\User;
use Illuminate\Database\Eloquent\Model;

class TokenBuyHistory extends Model
{
    protected $fillable = [
        'phase_id',
        'bank_id',
        'bank_ref',
        'wallet_id',
        'coin_id',
        'user_id',
        'amount',
        'buy_price',
        'payment_method',
        'status',
        'bank_slip',
        'trx_id',
        'payer_wallet',
        'token_id',
        'payer_coin',
        'blockchain_tx',
        'used_gas',
        'pay_amount',
        'buy_currency',
        'pay_currency',
        'is_admin_receive'
        
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
    public function coin()
    {
        return $this->belongsTo(Coin::class);
    }

    public function tokenDetails()
    {
        return $this->belongsTo(IcoToken::class,'token_id');
    }

    public function walletAddress()
    {
        return $this->belongsTo(WalletAddressHistory::class, 'wallet_id', 'wallet_id');
    }
}
