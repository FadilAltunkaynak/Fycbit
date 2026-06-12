<?php

namespace Modules\IcoLaunchpad\Entities;

use Illuminate\Database\Eloquent\Model;
use App\User;

class TokenWithdrawTransaction extends Model
{
    protected $fillable = [
        'user_id', 'request_amount', 'request_currency', 'convert_amount', 'convert_currency',
        'tran_type', 'approved_status', 'approved_by_id', 'fee', 'payment_details', 'payment_sleep'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
