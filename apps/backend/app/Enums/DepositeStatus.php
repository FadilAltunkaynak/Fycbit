<?php

namespace App\Enums;

enum DepositeStatus: int
{
    case PENDING = 0;
    case PROCESSING = 5;
    case SUCCESS = 1;
    case REJECTED = 2;
    case FAILED = 3;
    case EXPIRE = 99;

    public function getText(): string
    {
        return match ($this) {
            self::PENDING => __('Pending'),
            self::PROCESSING => __('Processing'),
            self::SUCCESS => __('Success'),
            self::REJECTED => __('Rejected'),
            self::FAILED => __('Failed'),
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

    public function statusHtml(): string
    {
        return match ($this) {
            self::PENDING => '<span class="text-warning">' . __('Pending') . '</span>',
            self::PROCESSING => '<span class="text-warning">' . __('Processing') . '</span>',
            self::SUCCESS => '<span class="text-success">' . __('Success') . '</span>',
            self::REJECTED => '<span class="text-danger">' . __('Rejected') . '</span>',
            self::FAILED => '<span class="text-danger">' . __('Failed') . '</span>',
            self::EXPIRE => '<span class="text-danger">' . __('Expired') . '</span>',
        };
    }

    public static function statusListHtml(): array
    {
        return [
            self::PENDING->value => '<span class="text-warning">' . __('Pending') . '</span>',
            self::PROCESSING->value => '<span class="text-warning">' . __('Processing') . '</span>',
            self::SUCCESS->value => '<span class="text-success">' . __('Success') . '</span>',
            self::REJECTED->value => '<span class="text-danger">' . __('Rejected') . '</span>',
            self::FAILED->value => '<span class="text-danger">' . __('Failed') . '</span>',
            self::EXPIRE->value => '<span class="text-danger">' . __('Expired') . '</span>',
        ];
    }
}