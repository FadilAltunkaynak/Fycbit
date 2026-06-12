<?php

namespace Modules\FutureTrade\Entities\Traits;

use App\User;
use Illuminate\Database\Eloquent\Builder;
use Modules\FutureTrade\DataObject\OrderCacheAbleData;
use Modules\FutureTrade\Emum\OrderStatusEnum;
use Modules\FutureTrade\Entities\FutureCoinPair;
use Modules\FutureTrade\Entities\FutureUserSetting;
use Modules\FutureTrade\Entities\FutureWallet;

trait OrderTrait
{
    public function futureWallet(): ?FutureWallet
    {
        if ($this->user_id) {
            debugLogger('future wallet lock from order table buy/sell');
            $this->wallet = FutureWallet::query()
                ->where('user_id', $this->user_id)
                ->where('coin_id', $this->trade_coin_id ?? 0)
                ->lockForUpdate()
                ->first();

            return $this->wallet;
        }

        return null;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function coinPair()
    {
        return $this->belongsTo(FutureCoinPair::class, 'future_coin_pair_id');
    }

    public function userLeverage()
    {
        return $this->belongsTo(FutureUserSetting::class, 'future_coin_pair_id', 'coin_pair_id');
    }

    public function getCacheAbleData(): OrderCacheAbleData
    {
        return OrderCacheAbleData::fromOrder($this);
    }

    public function scopePendingOrder(Builder $builder): Builder
    {
        return $builder->where('status', OrderStatusEnum::PENDING->value);
    }

    public function scopeCompleteOrder(Builder $builder): Builder
    {
        return $builder->where('status', OrderStatusEnum::COMPLETE->value);
    }

    public function scopeCancelOrder(Builder $builder): Builder
    {
        return $builder->where('status', OrderStatusEnum::CANCEL->value);
    }

    public function scopeByPair(Builder $builder, int $coinPair): Builder
    {
        return $builder->where('future_coin_pair_id', $coinPair);
    }
}
