<?php

namespace App\Enums;

enum DepositCollectionStatus: int
{
    case PENDING = 0;
    case PROCESSING = 5;
    case SUCCESS = 1;

    public function getText(): string
    {
        return match ($this) {
            self::PENDING => __('Pending'),
            self::PROCESSING => __('Processing'),
            self::SUCCESS => __('Success'),
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

    public function statusHtml(): string
    {
        return match ($this) {
            self::PENDING => '<span class="text-warning">' . __('Pending') . '</span>',
            self::PROCESSING => '<span class="text-warning">' . __('Processing') . '</span>',
            self::SUCCESS => '<span class="text-success">' . __('Success') . '</span>',
        };
    }

    public static function statusListHtml(): array
    {
        return [
            self::PENDING->value => '<span class="text-warning">' . __('Pending') . '</span>',
            self::PROCESSING->value => '<span class="text-warning">' . __('Processing') . '</span>',
            self::SUCCESS->value => '<span class="text-success">' . __('Success') . '</span>',
        ];
    }
}