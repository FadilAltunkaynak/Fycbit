<?php

namespace Modules\P2P\Entities;

use App\User;
use Modules\P2P\Entities\PGiftCard;
use Illuminate\Database\Eloquent\Model;

class PGiftCardOrder extends Model
{
    protected $fillable = [
        'uid',
        'order_id',
        'seller_id',
        'buyer_id',
        'p_gift_card_id',
        'order_type',
        'payment_currency_type',
        'currency_type',
        'price',
        'amount',
        'payment_time',
        'payment_expired_time',
        'payment_method_id',
        'payment_sleep',
        'status',
        'is_reported',
        'payment_status',
        'is_queue',
        'transaction_id',
        'admin_note',
        'who_cancelled',
        'is_success',
        'buyer_feedback_type',
        'buyer_feedback',
        'seller_feedback_type',
        'seller_feedback',
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function dispute_details()
    {
        return $this->hasOne(PGiftCardOrderDisputes::class, 'gift_card_order_id');
    }

    public function reporting_user()
    {
        return $this->belongsTo(User::class, 'reported_user');
    }

    public function p_gift_card()
    {
        return $this->hasOne(PGiftCard::class, 'id', 'p_gift_card_id');
    }
}
