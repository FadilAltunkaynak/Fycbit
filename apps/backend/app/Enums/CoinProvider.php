<?php

namespace App\Enums;

enum CoinProvider: int
{
    case COIN_PAYMENT = 1;
    case BITGO_API = 2;
    case CUSTOM_RPC_NODE = 3;

    public function getText(): string
    {
        return match ($this) {
            self::COIN_PAYMENT => __('Coin Payment Api'),
            self::BITGO_API => __('Bitgo Api'),
            self::CUSTOM_RPC_NODE => __('Custom RPC Node'),
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

    public static function isCoinPayment(int|string|null $value): bool
    {
        return self::tryFrom($value) === self::COIN_PAYMENT;
    }

    public static function isBitgo(int|string|null $value): bool
    {
        return self::tryFrom($value) === self::BITGO_API;
    }

    public static function isCustomRpc(int|string|null $value): bool
    {
        return self::tryFrom($value) === self::CUSTOM_RPC_NODE;
    }

    public static function externalGroup(): array
    {
        return [
            NetworkBase::COIN_PAYMENT,
            NetworkBase::BITGO_API,
        ];
    }
    public static function customRpcGroup(): array
    {
        return [
            NetworkBase::BITCOIN_API,
            NetworkBase::EVM_BASE_COIN,
            NetworkBase::TRON_BASE_COIN,
            NetworkBase::SOLANA_BASE_COIN,
        ];
    }

    public function getBaseTypes(): array
    {
        return match ($this) {
            self::COIN_PAYMENT => self::mapNetworkList([
                NetworkBase::COIN_PAYMENT,
            ]),
            self::BITGO_API => self::mapNetworkList([
                NetworkBase::BITGO_API,
            ]),
            self::CUSTOM_RPC_NODE => self::mapNetworkList([
                NetworkBase::BITCOIN_API,
                NetworkBase::TRON_BASE_COIN,
                NetworkBase::EVM_BASE_COIN,
                NetworkBase::SOLANA_BASE_COIN,
            ]),
        };
    }

    private static function mapNetworkList(array $networks): array
    {
        $mapped = [];
        foreach ($networks as $network) {
            $mapped[$network->value] = $network->getText();
        }
        return $mapped;
    }

    /**
     * Those Group Below Are Capable To Check Deposit By Transaction ID
     * @return array
     */
    public static function depositCheckableRpcNode(): array
    {
        return self::mapNetworkList([
            NetworkBase::EVM_BASE_COIN,
            NetworkBase::TRON_BASE_COIN,
            NetworkBase::SOLANA_BASE_COIN
        ]);
    }
}