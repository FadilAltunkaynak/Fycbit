<?php

namespace Modules\FutureTrade\Emum;

enum FutureWalletStatusEnum: int
{
    case ACTIVE = 1;
    case INACTIVE = 2;

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
