<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoinPaymentWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        "coin_id",
        "coin_type",
        "code",
        "wallet_id",
    ];
}
