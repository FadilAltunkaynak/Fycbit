<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FundTransferCoinListResponse extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'coin_type' => $this->coin_type,
            'coin_icon' => $this->coin_icon ?  show_image_path($this->coin_icon, 'coin/') : '',
            'balance' => $this->balance
        ];
    }
}
