<?php

namespace Modules\FutureTrade\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class MarketTradeList extends JsonResource
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
            'created_at' => $this->created_at,
            'price' => truncate_num($this->price, $this->coin_pair?->trade_decimal ?: 8),
            'previous_price' => truncate_num($this->last_price, $this->coin_pair?->trade_decimal ?: 8),
            'amount' => truncate_num($this->amount, $this->coin_pair?->trade_decimal ?: 8),
        ];
    }
}
