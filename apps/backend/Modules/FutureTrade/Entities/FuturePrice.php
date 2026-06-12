<?php

namespace Modules\FutureTrade\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\FutureTrade\Casts\NumberTrim;

class FuturePrice extends Model
{
    protected $fillable = [
        'future_coin_pair_id',
        'future_coin_pair_uid',
        'market_price',
        'mark_price',
        'index_price',
        'change_24h',
        'high_24h',
        'low_24h',
        'volume_24h_btc',
        'volume_24h_usdt',
    ];

    protected $casts = [
        'market_price' => NumberTrim::class,
        'mark_price' => NumberTrim::class,
        'index_price' => NumberTrim::class,
        'change_24h' => NumberTrim::class,
        'high_24h' => NumberTrim::class,
        'low_24h' => NumberTrim::class,
        'volume_24h_btc' => NumberTrim::class,
        'volume_24h_usdt' => NumberTrim::class,
    ];

    public function pair(): BelongsTo
    {
        return $this->belongsTo(FutureCoinPair::class, 'future_coin_pair_id');
    }
}
