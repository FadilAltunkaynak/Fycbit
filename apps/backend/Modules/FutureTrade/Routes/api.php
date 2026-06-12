<?php

use Illuminate\Support\Facades\Route;
use Modules\FutureTrade\Http\Controllers\Api\FutureCoinPairController;
use Modules\FutureTrade\Http\Controllers\Api\FutureLeverageSettingController;
use Modules\FutureTrade\Http\Controllers\Api\FutureOrderController;
use Modules\FutureTrade\Http\Controllers\Api\FutureTradeController;
use Modules\FutureTrade\Http\Controllers\Api\PositionController;
use Modules\FutureTrade\Http\Controllers\FutureChartController;
use Modules\FutureTrade\Http\Controllers\WalletController;
use Modules\FutureTrade\Services\WalletServices\WalletService;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('future')->group(function () {
    // on exchange page
    Route::get('default-coin-pair', [FutureCoinPairController::class, 'getDefaultCoinPair']);
    Route::get('get-candles', [FutureChartController::class, 'getCandles']);
    Route::get('coin-pairs', [FutureCoinPairController::class, 'getCoinPairList']);
    Route::get('market-overview-top-coin-list', [FutureCoinPairController::class, 'getMarketOverviewTopCoinList']);
    Route::get('coin-pair-details', [FutureCoinPairController::class, 'getCoinPairDetailsByCode']);
    Route::post('coin-pairs-details', [FutureCoinPairController::class, 'getMultipleCoinPairsDetails']);
    Route::get('all-active-coin-pairs', [FutureCoinPairController::class, 'getAllActiveCoinPairs']);
    Route::get('orderbook', [FutureOrderController::class, 'getOrderbook']);
    Route::get('max-leverage', [FutureLeverageSettingController::class, 'getMaxLeverage']);
    Route::get('market-trade-list/{coin_pair_uid}', [FutureTradeController::class, 'getMarketTradeList']);
    Route::get('get-all-leverage-settings', [FutureLeverageSettingController::class, 'getAllLeverageSettingByCoinPair']);
});

Route::prefix('future')->middleware([
    'maintenanceMode',
    'auth:api',
    'api-user',
    'last_seen'
])->group(function () {
    // on exchange page
    Route::get('user-margin-leverage', [FutureLeverageSettingController::class, 'getUserMarginLeverage']);
    Route::get('leverage-setting-data', [FutureLeverageSettingController::class, 'getLeverageSettingDataByLeverage']);
    Route::get('open-order-list', [FutureOrderController::class, 'getMyOrderList']);
    Route::get('user-assets', [WalletService::class, 'getUserAssets']);
    Route::post('margin-setting-update', [FutureLeverageSettingController::class, 'marginUpdate']);
    Route::post('leverage-setting-update', [FutureLeverageSettingController::class, 'leverageUpdate']);
    Route::post('order', [FutureOrderController::class, 'order']);
    Route::post('order-cancel', [FutureOrderController::class, 'orderCancel']);
    Route::get('wallet-details', [WalletController::class, 'getWalletDetails']);
    Route::get('wallet-details-balance', [WalletController::class, 'walletDetailsWithBalance']);
    Route::get('user-all-future-wallet', [WalletController::class, 'getUserAllFutureWallet']);
    Route::get('today-pnl', [WalletController::class, 'getTodayPnl']);
    Route::get('combined-wallet-data', [WalletController::class, 'getCombinedWalletData']);
    Route::get('open-order-and-position-cost', [WalletController::class, 'getOpenOrderAndPositionCost']);
    Route::get('open-order-and-position-amount', [WalletController::class, 'getOpenOrderAndPositionAmount']);
    Route::get('order/history', [FutureOrderController::class, 'getOrderHistory']);
    Route::get('trade/history', [FutureTradeController::class, 'getTradeHistory']);

    // Position API
    Route::prefix('position')->group(function () {
        Route::get('my-positions', [PositionController::class, 'getMyPositions']);
        Route::get('my-positions-history', [PositionController::class, 'getMyPositionHistory']);
        Route::get('user-margin-summary', [PositionController::class, 'getUserMarginSummary']);
        Route::get('user-assets-summary', [PositionController::class, 'getUserAssetsSummary']);
        Route::post('margin-update', [PositionController::class, 'updateIsolatedMargin']);
        Route::post('margin-tpsl-update', [PositionController::class, 'updateTpSl']);
        Route::post('cancel-position-tpsl', [PositionController::class, 'cancelTpSl']);
    });
});
