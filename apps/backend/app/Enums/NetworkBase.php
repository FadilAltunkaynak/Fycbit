<?php

namespace App\Enums;

enum NetworkBase: int
{
    case COIN_PAYMENT = 1;
    case BITCOIN_API = 2;
    case BITGO_API = 3;
    case ERC20_TOKEN = 4;
    case BEP20_TOKEN = 5;
    case TRON_BASE_COIN = 6;
    case EVM_BASE_COIN = 8;
    case SOLANA_BASE_COIN = 10;

    public function getText(): string
    {
        return match ($this) {
            self::COIN_PAYMENT => __('Coin Payment Api'),
            self::BITCOIN_API => __('Bitcoin Api'),
            self::BITGO_API => __('Bitgo Api'),
            self::TRON_BASE_COIN => __('Tron Base Coin'),
            self::EVM_BASE_COIN => __('EVM Base Coin'),
            self::SOLANA_BASE_COIN => __('Solana Base Coin'),
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

    public function getProvider(): int
    {
        return match ($this) {
            self::COIN_PAYMENT => CoinProvider::COIN_PAYMENT->value,
            self::BITGO_API => CoinProvider::BITGO_API->value,
            self::BITCOIN_API => CoinProvider::CUSTOM_RPC_NODE->value,
            self::TRON_BASE_COIN => CoinProvider::CUSTOM_RPC_NODE->value,
            self::EVM_BASE_COIN => CoinProvider::CUSTOM_RPC_NODE->value,
            self::SOLANA_BASE_COIN => CoinProvider::CUSTOM_RPC_NODE->value,
            self::ERC20_TOKEN => CoinProvider::CUSTOM_RPC_NODE->value,
            self::BEP20_TOKEN => CoinProvider::CUSTOM_RPC_NODE->value,
        };
    }

    public static function isCoinPayment(int|string|null $value): bool
    {
        return self::tryFrom($value) === self::COIN_PAYMENT;
    }

    public static function isBitgo(int|string|null $value): bool
    {
        return self::tryFrom($value) === self::BITGO_API;
    }

    public static function isBitcoin(int|string|null $value): bool
    {
        return self::tryFrom($value) === self::BITCOIN_API;
    }

    public static function isCustomNetwork(int|string|null $value): bool
    {
        return in_array($value, [
            self::EVM_BASE_COIN->value,
            self::TRON_BASE_COIN->value,
            self::SOLANA_BASE_COIN->value,
            self::ERC20_TOKEN->value,
            self::BEP20_TOKEN->value,
        ]);
    }

    public static function customNetworkGroup(): array
    {
        return [
            self::EVM_BASE_COIN->value,
            self::TRON_BASE_COIN->value,
            self::SOLANA_BASE_COIN->value,
            self::ERC20_TOKEN->value,
            self::BEP20_TOKEN->value,
        ];
    }
}