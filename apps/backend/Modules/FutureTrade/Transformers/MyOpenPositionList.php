<?php

namespace Modules\FutureTrade\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Modules\FutureTrade\Emum\OrderMethod;
use Modules\FutureTrade\Emum\OrderType;

class MyOpenPositionList extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'symbol' => $this?->coinPair?->code ?? 'N/A',
            'order_type' => OrderType::tryFrom($this->order_type)?->label(true) ?: 'N/A',
            'price' => trim_num($this->price, $this->coinPair?->trade_decimal ?: 8),
            'amount' => trim_num($this->amount, $this->coinPair?->trade_decimal ?: 8),
            'base_coin_code' => $this?->coinPair?->base_coin_code ?? 'N/A',
            'trade_coin_code' => $this?->coinPair?->trade_coin_code ?? 'N/A',
            'mark_price' => cache_service()->getMarkPrice($this?->coinPair?->id ?? 0) ?: 0,
            'tp_price' => trim_num($this->tp_price, $this->coinPair?->trade_decimal ?: 8),
            'sl_price' => trim_num($this->sl_price, $this->coinPair?->trade_decimal ?: 8),
            // 'liq_price' => 0,
            // 'margin_ration' => 0,
            // 'margin' => 0,
            // 'margin_mode' => 0,
            // 'pnl' => 0,
            // 'roi' => 0,
            'liq_price' => 0,
            'margin_ration' => 0,
            'margin' => 0,
            'margin_mode' => 0,
            'pnl' => 0,
            'roi' => 0,
        ];
    }
}
