<?php

namespace App\Enums;

enum YesNoStatus2: int
{
    case YES = 1;
    case NO = 0;

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