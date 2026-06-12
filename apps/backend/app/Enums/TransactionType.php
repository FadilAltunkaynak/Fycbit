<?php

namespace App\Enums;

enum TransactionType: int
{
    case DEPOSIT = 1;
    case WITHDRAWAL = 2;

    public function getText(): string
    {
        return match ($this) {
            self::DEPOSIT => __('Deposit'),
            self::WITHDRAWAL => __('Withdrawal'),
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

    public static function isDeposit(int|string|null $value): bool
    {
        return self::tryFrom($value) === self::DEPOSIT;
    }

    public static function isWithdrawal(int|string|null $value): bool
    {
        return self::tryFrom($value) === self::WITHDRAWAL;
    }
}