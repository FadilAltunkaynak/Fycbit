<?php

namespace Modules\FutureTrade\Emum;

use App\Enums\Traits\RenderTrait;

enum OrderType: int
{
    use RenderTrait;
    case BUY = 1;
    case SELL = 2;

    /**
     * Get Status Label
     */
    public function label(bool $futureStyle = false): string
    {
        return match ($this) {
            self::BUY => $futureStyle ? __('Long') : __('Buy'),
            self::SELL => $futureStyle ? __('Short') : __('Sell'),
        };
    }

    public function bothLabel(): string
    {
        return match ($this) {
            self::BUY => __('Long').'/'.__('Buy'),
            self::SELL =>  __('Short').'/'.__('Sell'),
        };
    }

    public function bothLabelWithColor(): string
    {
        $value = $this->bothLabel();

        return match ($this) {
            self::BUY => '<button class="btn btn-sm btn-success">'.$value.'</button>',
            self::SELL => '<button class="btn btn-sm btn-danger">'.$value.'</button>',
        };
    }

    public static function is_buy(int $type)
    {
        $type = self::tryFrom($type);
        return $type === self::BUY;
    }
    
    public static function is_sell(int $type)
    {
        $type = self::tryFrom($type);
        return $type === self::SELL;
    }
}
