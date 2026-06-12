<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminNetworkBalance extends Model
{
    use HasFactory;
    protected $fillable = [
        "key_id",
        "coin_id",
        "balance",
    ];
}
