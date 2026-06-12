<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWithdrawalAddress extends Model
{
    use HasFactory;
    protected $fillable = [
        "uid",
        "user_id",
        "label",
        "currency_id",
        "network_id",
        "address",
        "is_universal",
        "status",
        "memo",
    ];
}
