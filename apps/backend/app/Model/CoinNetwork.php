<?php

namespace App\Model;

use App\Model\Coin;
use App\Model\Network;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CoinNetwork extends Model
{
    use HasFactory;
    protected $fillable = [
        'uid',
        'network_id',
        'currency_id',
        'type',
        'contract_address',
        'withdrawal_fees_type',
        'withdrawal_fees',
        'status',
        'coin_decimal'
    ];

    public function coin()
    {
        return $this->belongsTo(Coin::class, "currency_id");
    }
    
    public function network()
    {
        return $this->belongsTo(Network::class);
    }
}
