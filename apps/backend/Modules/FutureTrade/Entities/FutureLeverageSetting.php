<?php

namespace Modules\FutureTrade\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FutureLeverageSetting extends Model
{
    protected $fillable = [
        'uid',
        'coin_pair_uid',
        'min_position_amount',
        'max_position_amount',
        'max_leverage',
        'maintenance_margin_rate',
        'maintenance_amount',
    ];

    public function scopeByCoinPair(Builder $builder, string $coin_pair_uid)
    {
        return $builder->where('coin_pair_uid', $coin_pair_uid);
    }

    public function scopeMaxLeverage(Builder $builder, string $coin_pair_uid)
    {
        return $builder->byCoinPair($coin_pair_uid)->max('max_leverage');
    }
}
