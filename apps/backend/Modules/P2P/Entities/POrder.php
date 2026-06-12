<?php

namespace Modules\P2P\Entities;

use Modules\P2P\Entities\P2PWallet;
use Illuminate\Database\Eloquent\Model;
use App\User;

class POrder extends Model
{
    protected $fillable = [
        'uid',
        'order_id',
        'buyer_id',
        'seller_id',
        'buyer_wallet_id',
        'seller_wallet_id',
        'sell_id',
        'buy_id',
        'coin_type',
        'currency',
        'rate',
        'amount',
        'price',
        'seller_fees',
        'buyer_fees',
        'seller_fees_percentage',
        'buyer_fees_percentage',
        'status',
        'is_reported',
        'payment_status',
        'is_queue',
        'payment_id',
        'payment_sleep',
        'transaction_id',
        'payment_time',
        'payment_expired_time',
        'admin_note',
        'who_opened',
        'who_cancelled',
        'is_success',
        'seller_feedback',
        'seller_feedback_type',
        'buyer_feedback',
        'buyer_feedback_type',
    ];

    public function sellerWallet()
    {
        return $this->hasOne(P2PWallet::class,'id','seller_wallet_id');
    }
    
    public function buyerWallet()
    {
        return $this->hasOne(P2PWallet::class,'id','buyer_wallet_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function reported_user()
    {
        return $this->belongsTo(User::class, 'is_reported');
    }

    public function dispute_details()
    {
        return $this->hasOne(POrderDispute::class, 'order_id');
    }
}
