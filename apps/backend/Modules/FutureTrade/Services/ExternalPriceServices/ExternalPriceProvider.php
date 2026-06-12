<?php

namespace Modules\FutureTrade\Services\ExternalPriceServices;

enum ExternalPriceProvider: string
{
    case BINANCE = 'binance';
    case COINGECKO = 'coingecko';
    case KRAKEN = 'kraken';
    case OKX = 'okx';
    case GATEIO = 'gateio';
    case KUCOIN = 'kucoin';
    case CRYPTOCOMPARE = 'cryptocompare';
}
