<?php

namespace Modules\FutureTrade\Emum;

use App\Enums\Traits\RenderTrait;

enum OrderStatusEnum: int
{
    use RenderTrait;
    case PENDING = 0;
    case COMPLETE = 1;
    case CANCEL = 2;
    case PROCESSING = 3;

    /**
     * Get Type Label
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('Open'),
            self::COMPLETE => __('Complete'),
            self::CANCEL => __('Cancel'),
            self::PROCESSING => __('Processing'),
        };
    }

    public function bothLabelWithColor(): string
    {
        $value = $this->label();

        return match ($this) {
            self::PENDING => '<button class="btn btn-sm btn-success">'.$value.'</button>',
            self::COMPLETE => '<button class="btn btn-sm btn-info">'.$value.'</button>',
            self::CANCEL => '<button class="btn btn-sm btn-danger">'.$value.'</button>',
            self::PROCESSING => '<button class="btn btn-sm btn-warning">'.$value.'</button>',
        };
    }
}
