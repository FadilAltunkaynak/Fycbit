<?php

namespace App\Enums;

enum WalletNetworkStatus: int
{
    case ACTIVE = 1;
    case INACTIVE = 2;
    case EXPIRE = 99;

    public function getText(): string
    {
        return match ($this) {
            self::ACTIVE => __('Active'),
            self::INACTIVE => __('Inactive'),
            self::EXPIRE => __('Expired'),
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