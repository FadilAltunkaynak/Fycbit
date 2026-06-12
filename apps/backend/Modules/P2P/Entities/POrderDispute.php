<?php

namespace Modules\P2P\Entities;

use App\User;
use Illuminate\Database\Eloquent\Model;

class POrderDispute extends Model
{
    protected $fillable = [
        'uid',
        'order_id',
        'user_id',
        'reported_user',
        'reason_heading',
        'details',
        'image',
        'status',
        'updated_by',
        'assigned_admin',
        'expired_at'
    ];

    public function admin() {
        return $this->belongsTo(User::class,'assigned_admin');
    }

    public function assigned_by() 
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function assigned_to() 
    {
        return $this->belongsTo(User::class, 'assigned_admin');
    }
}
