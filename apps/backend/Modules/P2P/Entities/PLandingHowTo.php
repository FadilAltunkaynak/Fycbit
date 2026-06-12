<?php

namespace Modules\P2P\Entities;

use Illuminate\Database\Eloquent\Model;

class PLandingHowTo extends Model
{
    protected $fillable = [
        'type', 'header', 'description', 'status'
    ];
}
