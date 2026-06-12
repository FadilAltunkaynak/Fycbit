<?php

namespace App\Enums;

enum WalletAddressStatus: int
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

    public function statusHtml()
    {
        return match ($this) {
            self::ACTIVE => '<span class="text-success">' . __('Active') . '</span>',
            self::INACTIVE => '<span class="text-warning">' . __('Inactive') . '</span>',
            self::EXPIRE => '<span class="text-danger">' . __('Expired') . '</span>',
        };
    }
}