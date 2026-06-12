<?php

namespace App\Model;

use App\Model\Network;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdminWalletKey extends Model
{
    use HasFactory;
    protected $fillable = [
        "uid",
        "network_id",
        "address",
        "pv",
        "creation_type",
        "status",
    ];

    public function network()
    {
        return $this->belongsTo(Network::class, "network_id");
    }
}
