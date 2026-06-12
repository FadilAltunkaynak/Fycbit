<?php

namespace Modules\FutureTrade\Emum;

use App\Enums\Traits\ArrayTrait;
use App\Enums\Traits\RenderTrait;

enum PositionBroadcastEventEnum: string
{
    use ArrayTrait, RenderTrait;
    case POSITION = 'future.position';
    case ASSET_BALANCE = 'future.asset.balance';
    case MARGIN_DETAILS = 'future.margin.details';
}
