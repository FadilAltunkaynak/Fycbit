<?php

namespace App\Enums;

enum CoinType: int
{
    case TOKEN_COIN = 1;
    case NATIVE_COIN = 2;

    public function getText(): string
    {
        return match ($this) {
            self::TOKEN_COIN => __('Token'),
            self::NATIVE_COIN => __('Native'),
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

    public static function isToken(int|string|null $value): bool
    {
        return self::tryFrom($value) === self::TOKEN_COIN;
    }

    public static function isNative(int|string|null $value): bool
    {
        return self::tryFrom($value) === self::NATIVE_COIN;
    }
}