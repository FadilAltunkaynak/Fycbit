<?php

namespace Modules\FutureTrade\DataObject;

use Modules\FutureTrade\Entities\FutureCoinPair;

class Calculate24HourPriceDto
{
    public function __construct(
        public int $base_coin_id,
        public int $trade_coin_id,
    ) {}

    public static function fromCoinPair(FutureCoinPair $pair): self
    {
        return new self(
            base_coin_id: $pair->base_coin_id,
            trade_coin_id: $pair->trade_coin_id,
        );
    }
    public static function fromParam(int $base_id, $trade_id): self
    {
        return new self(
            base_coin_id: $base_id,
            trade_coin_id: $trade_id,
        );
    }
}
