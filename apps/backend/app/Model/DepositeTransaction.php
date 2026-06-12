<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DepositeTransaction extends Model
{
    protected $fillable = [
        'address',
        'fees',
        'sender_wallet_id',
        'receiver_wallet_id',
        'address_type',
        'coin_type',
        'amount',
        'btc',
        'doller',
        'transaction_id',
        'status',
        'confirmations',
        'from_address',
        'updated_by',
        'network_type',
        'is_admin_receive',
        'received_amount',
        'network_id',
        'block_number',
        'coin_id',
        'reject_note'
    ];

    public function coin()
    {
        return $this->belongsTo(Coin::class, 'coin_type', 'coin_type');
    }
    public function networkInfo()
    {
        return $this->belongsTo(Network::class, 'network_id');
    }
    public function senderWallet()
    {
        return $this->belongsTo(Wallet::class, 'sender_wallet_id', 'id');
    }
    public function receiverWallet()
    {
        return $this->belongsTo(Wallet::class, 'receiver_wallet_id', 'id');
    }
}
