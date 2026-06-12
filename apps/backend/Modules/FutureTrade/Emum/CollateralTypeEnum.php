<?php

namespace Modules\FutureTrade\Emum;

use App\Enums\Traits\ArrayTrait;
use App\Enums\Traits\RenderTrait;

enum CollateralTypeEnum: int
{
    use ArrayTrait, RenderTrait;
    case USDT_M = 1;
    // case CION_M = 2;

    /**
     * Get Type Label
     */
    public function label(): string
    {
        return match ($this) {
            self::USDT_M => 'USDⓈ-M',
            // self::CION_M => 'COIN-M',
        };
    }
}
