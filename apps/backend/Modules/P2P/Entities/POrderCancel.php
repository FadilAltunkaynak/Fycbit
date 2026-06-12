<?php

namespace Modules\P2P\Entities;

use Illuminate\Database\Eloquent\Model;

class POrderCancel extends Model
{
    protected $fillable = [
        'order_id', 'type', 'user_id', 'partner_id', 'reason_heading', 'details'
    ];
}
