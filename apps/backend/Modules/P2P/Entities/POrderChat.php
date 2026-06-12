<?php

namespace Modules\P2P\Entities;

use App\User;
use Illuminate\Database\Eloquent\Model;

class POrderChat extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'order_id',
        'dispute_id',
        'message',
        'file',
        'seen'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'sender_id' );
    }

    public function receiver()
    {
        return $this->belongsTo(User::class,'receiver_id' );
    }
}
