<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NetworkBalance extends Model
{
    use HasFactory;
    protected $fillable = [
        "wallet_address_id",
        "coin_network_id",
        "balance",
    ];
}
