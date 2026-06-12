<?php

namespace Modules\FutureTrade\Emum;

use App\Enums\Traits\RenderTrait;

enum OrderMethod: int
{
    use RenderTrait;
    case LIMIT = 1;
    case MARKET = 2;
    case STOP_LIMIT = 3;

    /**
     * Get Status Label
     */
    public function label(): string
    {
        return match ($this) {
            self::LIMIT => __('Limit'),
            self::MARKET => __('Market'),
            self::STOP_LIMIT => __('Stop-Limit'),
        };
    }
}
