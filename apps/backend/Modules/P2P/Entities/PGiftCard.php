<?php

namespace Modules\P2P\Entities;

use App\User;
use App\Model\GiftCard;
use Illuminate\Database\Eloquent\Model;

class PGiftCard extends Model
{
    protected $fillable = [
        'uid',
        'user_id',
        'add_type',
        'gift_card_id',
        'payment_currency_type',
        'currency_type',
        'price',
        'terms_condition',
        'country',
        'time_limit',
        'auto_reply',
        'user_registered_before',
        'minimum_coin_hold',
        'payment_method',
        'status'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function gift_card()
    {
        return $this->hasOne(GiftCard::class, 'id', 'gift_card_id');
    }
}
