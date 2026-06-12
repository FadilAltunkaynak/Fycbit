<?php

namespace Modules\FutureTrade\Emum;

use App\Enums\Traits\RenderTrait;

enum MarginModeEnum: int
{
    use RenderTrait;
    case CROSS = 1;
    case ISOLATED = 2;

    /**
     * Get Status Label
     */
    public function label(bool $futureStyle = false): string
    {
        return match ($this) {
            self::CROSS => __('Cross'),
            self::ISOLATED => __('Isolated'),
        };
    }
}
