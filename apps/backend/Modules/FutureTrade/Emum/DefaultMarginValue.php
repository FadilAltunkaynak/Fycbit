<?php

namespace Modules\FutureTrade\Emum;

use App\Enums\Traits\RenderTrait;

enum DefaultMarginValue: int
{
    use RenderTrait;
    case MARGIN_AMOUNT = 0;
    case MARGIN_RATE = 1;
}
