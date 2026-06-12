<?php

namespace App\Enums;

enum YesNoStatus: int
{
    case YES = 1;
    case NO = 2;

    public function getText(): string
    {
        return match ($this) {
            self::YES => __('Yes'),
            self::NO => __('No'),
        };
    }

    public static function getList(): array
    {
        $list = [];
        foreach (self::cases() as $case) {
            $list[$case->value] = $case->getText();
        }
        return $list;
    }
}