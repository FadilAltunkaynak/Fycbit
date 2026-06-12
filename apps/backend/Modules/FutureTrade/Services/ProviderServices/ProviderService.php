<?php

namespace Modules\FutureTrade\Services\ProviderServices;

use Illuminate\Foundation\Application;
use Modules\FutureTrade\Entities\FutureBuy;
use Modules\FutureTrade\Entities\FutureCoinPair;
use Modules\FutureTrade\Entities\FuturePosition;
use Modules\FutureTrade\Entities\FutureSell;
use Modules\FutureTrade\Entities\FutureTrade;
use Modules\FutureTrade\Observers\FutureBuyOrderObserver;
use Modules\FutureTrade\Observers\FutureCoinPairObserver;
use Modules\FutureTrade\Observers\FuturePositionObserver;
use Modules\FutureTrade\Observers\FutureSellOrderObserver;
use Modules\FutureTrade\Observers\FutureTradeObserver;
use Modules\FutureTrade\Repositories\CoinPairRepository\CoinPairRepository;
use Modules\FutureTrade\Repositories\CoinPairRepository\ICoinPairRepository;
use Modules\FutureTrade\Repositories\OrderRepository\IOrderRepository;
use Modules\FutureTrade\Repositories\OrderRepository\OrderRepository;
use Modules\FutureTrade\Repositories\PositionRepository\IPositionRepository;
use Modules\FutureTrade\Repositories\PositionRepository\PositionRepository;
use Modules\FutureTrade\Repositories\TradeRepository\FutureTradeRepository;
use Modules\FutureTrade\Repositories\TradeRepository\IFutureTradeRepository;
use Modules\FutureTrade\Repositories\WalletRepository\FutureWalletRepository;
use Modules\FutureTrade\Repositories\WalletRepository\IFutureWalletRepository;
use Modules\FutureTrade\Services\CacheServices\CacheServices;

class ProviderService
{
    /**
     * Set dependence on application container
     */
    public static function set_dependence(Application $app): void
    {
        // coin pair repository
        $app->singleton(ICoinPairRepository::class, CoinPairRepository::class);
        $app->alias(ICoinPairRepository::class, 'future-coin-pair-repository');

        // order repository
        $app->singleton(IOrderRepository::class, OrderRepository::class);
        $app->alias(IOrderRepository::class, 'future-order-repository');

        // wallet repository
        $app->singleton(IFutureWalletRepository::class, FutureWalletRepository::class);
        $app->alias(IFutureWalletRepository::class, 'future-wallet-repository');

        // Trade repository
        $app->singleton(IFutureTradeRepository::class, FutureTradeRepository::class);
        $app->alias(IFutureTradeRepository::class, 'future-trade-repository');

        // position
        $app->singleton(IPositionRepository::class, PositionRepository::class);
        $app->alias(IPositionRepository::class, 'future-position-repository');

        // position
        $app->singleton(CacheServices::class);
        $app->alias(CacheServices::class, 'future-cache');

    }

    public static function addRedisConfig(): void
    {
        $redisConfig = [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 5,
        ];
        config(['database.redis.future_trade_cache' => $redisConfig]);
    }

    public static function setObservers(): void
    {
        FutureCoinPair::observe(FutureCoinPairObserver::class);
        FutureSell::observe(FutureSellOrderObserver::class);
        FutureBuy::observe(FutureBuyOrderObserver::class);
        FutureTrade::observe(FutureTradeObserver::class);
        FuturePosition::observe(FuturePositionObserver::class);
    }
}