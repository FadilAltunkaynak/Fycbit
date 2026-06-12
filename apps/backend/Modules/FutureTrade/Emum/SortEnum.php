<?php

namespace Modules\FutureTrade\Emum;

use App\Enums\Traits\RenderTrait;

enum SortEnum: int
{
    use RenderTrait;
    case ASC = 1;
    case DESC = 2;

    /**
     * Get Type Label
     */
    public function label(): string
    {
        return match ($this) {
            self::ASC => 'asc',
            self::DESC => 'desc',
        };
    }
}
