<?php

namespace App\Enums;

enum FeesType: int
{
    case FIXED = 1;
    case PERCENTAGE = 2;

    public function getText(): string
    {
        return match ($this) {
            self::FIXED => __('Fixed'),
            self::PERCENTAGE => __('Percentage'),
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

    /**
     * Summary of calculateFees
     * @param int $type
     * @param mixed $baseAmount
     * @param mixed $feesValue
     * @return string
     */
    public static function calculateFees(int $type, mixed $baseAmount, mixed $feesValue): string
    {
        if (self::tryFrom($type) === self::FIXED)
            return (string) $feesValue;

        $amount = self::trimNum($baseAmount);
        $fees = bcdivx(bcmulx($feesValue, $amount), '100');

        return $fees;
    }

    /**
     * Summary of truncateNum
     * @param mixed $value
     * @param int $scale
     * @return string
     */
    private static function trimNum(mixed $value, int $decimal = 8): string
    {
        $value = is_string($value) ? floatval($value) : $value;
        $number = number_format($value, $decimal, '.', '');
        $result = rtrim(rtrim($number, '0'), '.');

        return (string) $result;
    }
}