<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportedNetwork extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
        'slug',
        'name',
        'network_type',
        'chain_id',
        'native_currency',
        'base_url',
        'token_endpoint',
        'address_endpoint',
        'tx_endpoint',
        'gas_limit',
        'gas_price',
        'status',
        "is_manually"
    ];
}
