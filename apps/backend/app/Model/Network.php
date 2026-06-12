<?php

namespace App\Model;

use App\Events\NetworkCreated;
use App\Model\Coin;
use App\Model\NotifiedBlock;
use App\Model\AdminWalletKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Network extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "slug",
        "description",
        "block_confirmation",
        "provider_type",
        "base_type",
        "rpc_url",
        "wss_url",
        "explorer_url",
        "chain_id",
        "status",
        "logo",
        'from_block_number',
        'to_block_number'
    ];

    protected static function booted()
    {
        static::created(function ($network) {
            event(new NetworkCreated($network));
        });
    }

    public function admin_wallet()
    {
        return $this->belongsTo(AdminWalletKey::class, 'id', 'network_id');
    }

    public function block()
    {
        return $this->hasOne(NotifiedBlock::class, 'network_id');
    }
}
