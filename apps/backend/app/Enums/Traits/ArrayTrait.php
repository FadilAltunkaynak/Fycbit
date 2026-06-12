<?php

namespace App\Enums\Traits;

trait ArrayTrait
{
    /**
     * Get Values As Array (Only Enum Value)
     * @return array
     */
    public static function values(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }

    /**
     * Get all available versions as an associative array
     */
    public static function toArray(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn($case) => $case->label(), self::cases())
        );
    }
}