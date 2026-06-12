<?php

namespace App\Model\DynamicBank;

use App\Model\DynamicBank\BankForm;
use Illuminate\Database\Eloquent\Model;
use Modules\P2P\Entities\PPaymentMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\BankService\Enums\BankFormAccessType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Query\Builder as QueryBuilder;

class BankRecord extends Model
{
    use HasFactory;
    protected $fillable = [
        "form_id",
        "user_id",
        "access",
        "bank",
        "status",
        "is_admin",
    ];

    public function bank_form(): BelongsTo
    {
        return $this->belongsTo(BankForm::class, "form_id");
    }

    public function paymentMethod()
    {
        return $this->hasOneThrough(
            PPaymentMethod::class,
            BankForm::class,
            'id',
            'payment_type',
            'form_id',
            'id'
        );
    }

    public function scopeGetUserPaymentMethod(Builder $builder, int $user_id = 0): Builder|QueryBuilder
    {
        return $builder->where(['user_id' => $user_id, 'status' => STATUS_ACTIVE])
            ->where("access", "LIKE", "%" . BankFormAccessType::P2P->value . "%")
            ->with(['bank_form.paymentMethod']);
    }

    public function scopeGetBankFormRelation(Builder $builder): Builder|QueryBuilder
    {
        return $builder->with(['bank_form.fields', 'bank_form.paymentMethod']);
    }

    public function scopeGetOnlyP2p(Builder $builder): Builder|QueryBuilder
    {
        return $builder->where("access", "LIKE", "%" . BankFormAccessType::P2P->value . "%");
    }
}
