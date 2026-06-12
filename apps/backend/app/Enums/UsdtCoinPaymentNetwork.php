<?php

namespace App\Enums;

enum UsdtCoinPaymentNetwork: string
{
    case OMNILAYER = 'USDT';
    case BEP20 = 'USDT.BEP20';
    case ERC20 = 'USDT.ERC20';
    case SOLANA = 'USDT.SOL';
    case TRC20 = 'USDT.TRC20';

    public function getText(): string
    {
        return match ($this) {
            self::OMNILAYER => __('Tether USD (Omni Layer)'),
            self::BEP20 => __('Tether USD (BEP20)'),
            self::ERC20 => __('Tether USD (ERC20)'),
            self::SOLANA => __('Tether USD (Solana)'),
            self::TRC20 => __('Tether USD (Tron/TRC20)'),
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