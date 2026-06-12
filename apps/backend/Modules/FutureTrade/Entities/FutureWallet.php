<?php

namespace Modules\FutureTrade\Entities;

use App\Model\Coin;
use App\Model\Wallet;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\FutureTrade\Emum\FutureWalletStatusEnum;

class FutureWallet extends Model
{
    protected $fillable = [
        'name',
        'user_id',
        'coin_id',
        'balance',
        'available_balance',
        'in_order_balance',
        'margin_balance',
        'funding_fee_received',
        'pnl_total',
        'frozen_balance',
        'status',
    ];

    protected $casts = [
        'status' => FutureWalletStatusEnum::class,
    ];

    /**
     * Get Related Coin
     */
    public function coin(): BelongsTo
    {
        return $this->belongsTo(Coin::class);
    }

    /**
     * Get Related User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get Related Wallet
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
