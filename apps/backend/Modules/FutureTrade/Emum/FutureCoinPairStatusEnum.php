<?php

namespace Modules\FutureTrade\Emum;

use App\Enums\Traits\RenderTrait;

enum FutureCoinPairStatusEnum: int
{
    use RenderTrait;
    case ACTIVE = 1;
    case INACTIVE = 0;

    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }

    /**
     * Get Status Label
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => __('Active'),
            self::INACTIVE => __('Inactive'),
        };
    }
}
