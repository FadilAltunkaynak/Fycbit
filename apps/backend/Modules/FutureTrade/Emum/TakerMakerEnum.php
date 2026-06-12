<?php

namespace Modules\FutureTrade\Emum;

use App\Enums\Traits\RenderTrait;

enum TakerMakerEnum: int
{
    use RenderTrait;
    case TAKER = 1;
    case MAKER = 2;

    /**
     * Get Type Label
     */
    public function label(): string
    {
        return match ($this) {
            self::TAKER => __('Taker'),
            self::MAKER => __('Maker'),
        };
    }

    public function bothLabelWithColor(): string
    {
        $value = $this->label();

        return match ($this) {
            self::TAKER => '<button class="btn btn-sm btn-success">'.$value.'</button>',
            self::MAKER => '<button class="btn btn-sm btn-info">'.$value.'</button>',
        };
    }
}
