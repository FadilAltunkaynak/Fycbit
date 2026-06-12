<?php

namespace Modules\P2P\Entities;

use App\Model\DynamicBank\BankForm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PPaymentMethod extends Model
{
    protected $fillable = ['uid','name','payment_type','country','note','logo','status'];

    public function bank_form(): BelongsTo
    {
        return $this->belongsTo(BankForm::class,'payment_type');
    } 
}
