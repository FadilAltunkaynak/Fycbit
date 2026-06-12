<?php

namespace App\Enums;

enum FundEnum: int
{
    case SPOT = 1;
    case FUTURE = 2;
    case P2P = 3;

    public static function values(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }

    public static function getFunds(): array
    {
        $data = [];
        foreach(self::cases() as $value)
        $data[] = ['value' => $value->value, 'label' => $value->label()];
        return $data;
    }

    public function label(): string
    {
        return match ($this) {
            self::SPOT => __('Spot'),
            self::FUTURE => __('Future'),
            // self::P2P => __('P2P'),
        };
    }
}