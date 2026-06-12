<?php

namespace Modules\FutureTrade\Emum;

use App\Enums\Traits\RenderTrait;

enum PositionDirection: int
{
    use RenderTrait;

    case EQUAL = 0;
    case BUY = 1;
    case SELL = 2;

    /**
     * Get Status Label
     */
    public function label(bool $futureStyle = false): string
    {
        return match ($this) {
            self::EQUAL => $futureStyle ? __('Equal') : __('Equal'),
            self::BUY => $futureStyle ? __('Long') : __('Buy'),
            self::SELL => $futureStyle ? __('Short') : __('Sell'),
        };
    }
}