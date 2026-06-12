<?php

namespace Modules\P2P\Entities;

use Illuminate\Database\Eloquent\Model;

class PPaymentTime extends Model
{
    protected $fillable = ['status','time','uid'];
}
