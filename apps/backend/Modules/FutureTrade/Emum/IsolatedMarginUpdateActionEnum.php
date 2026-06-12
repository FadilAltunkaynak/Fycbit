<?php

namespace Modules\FutureTrade\Emum;

use App\Enums\Traits\RenderTrait;

enum IsolatedMarginUpdateActionEnum: int
{
    use RenderTrait;

    case CREDIT = 1;
    case DEBIT = 2;

    /**
     * Get Action Label
     */
    public function label(): string
    {
        return match ($this) {
            self::CREDIT => __('Credit'),
            self::DEBIT => __('Debit'),
        };
    }
}
