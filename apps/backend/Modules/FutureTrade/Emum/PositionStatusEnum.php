<?php

namespace Modules\FutureTrade\Emum;

use App\Enums\Traits\RenderTrait;

enum PositionStatusEnum: int
{
    use RenderTrait;
    case OPEN = 1;
    case UPDATED = 2;
    case CLOSED = 3;
    case LIQUIDATED = 4;

    /**
     * Get Status Label
     */
    public function label(): string
    {
        return match ($this) {
            self::OPEN => __('Open'),
            self::UPDATED => __('Updated'),
            self::CLOSED => __('Closed'),
            self::LIQUIDATED => __('Liquidated'),
        };
    }

    public function labelWithColor(): string
    {
        $value = $this->label();

        return match ($this) {
            self::OPEN => '<button class="btn btn-sm btn-success">'.$value.'</button>',
            self::UPDATED => '<button class="btn btn-sm btn-info">'.$value.'</button>',
            self::CLOSED => '<button class="btn btn-sm btn-dark">'.$value.'</button>',
            self::LIQUIDATED => '<button class="btn btn-sm btn-danger">'.$value.'</button>',
        };
    }
}