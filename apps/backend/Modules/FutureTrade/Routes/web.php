<?php

use Illuminate\Support\Facades\Route;
use Modules\FutureTrade\Http\Controllers\FutureCoinPairController;
use Modules\FutureTrade\Http\Controllers\FutureDashboardController;
use Modules\FutureTrade\Http\Controllers\FutureLeverageSettingController;
use Modules\FutureTrade\Http\Controllers\FutureOrderController;
use Modules\FutureTrade\Http\Controllers\FuturePositionHistoryController;
use Modules\FutureTrade\Http\Controllers\FuturePositionsController;
use Modules\FutureTrade\Http\Controllers\FutureTradeController;
use Modules\FutureTrade\Http\Controllers\FutureTradeSettingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('future')->as('future.')->middleware(['auth', 'admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [FutureDashboardController::class, 'dashboard'])->name('dashboard');

    // Coin Pairs
    Route::prefix('coin-pairs')->as('coin-pairs.')->group(function () {
        Route::get('/', [FutureCoinPairController::class, 'list'])->name('list');
        Route::post('/', [FutureCoinPairController::class, 'store'])->name('store');
        Route::get('/form/{uid?}', [FutureCoinPairController::class, 'edit'])->name('edit');
        Route::get('/bot-settings/{uid}', [FutureCoinPairController::class, 'editBotSettings'])->name('bot-settings.edit');
        Route::post('/bot-settings/{uid}', [FutureCoinPairController::class, 'updateBotSettings'])->name('bot-settings.update');
        Route::post('/bot-settings/{uid}/clean-chart', [FutureCoinPairController::class, 'cleanChartData'])->name('bot-settings.clean-chart');
        Route::post('/bot-settings/{uid}/clean-bot', [FutureCoinPairController::class, 'cleanBotData'])->name('bot-settings.clean-bot');
        Route::post('/status', [FutureCoinPairController::class, 'changeStatus'])->name('status.update');
        Route::get('/chart-update/{uid}', [FutureCoinPairController::class, 'chartUpdate'])->name('chart-update');
        Route::get('/delete/{uid}', [FutureCoinPairController::class, 'delete'])->name('delete');
    });

    // Leverage Setting
    Route::prefix('leverage/{coin_pair_uid}')->as('leverage.')->group(function () {
        Route::get('/', [FutureLeverageSettingController::class, 'list'])->name('list');
        Route::post('/', [FutureLeverageSettingController::class, 'store'])->name('store');
        Route::get('/form/{uid?}', [FutureLeverageSettingController::class, 'edit'])->name('edit');
        Route::get('/delete/{uid}', [FutureLeverageSettingController::class, 'delete'])->name('delete');
    });

    // Orders
    Route::prefix('order')->as('order.')->group(function () {
        Route::get('/get-order-modal-data', [FutureOrderController::class, 'getOrder'])->name('get-modal-order');
        Route::get('/buy', [FutureOrderController::class, 'buyOrderList'])->name('buy.list');
        Route::get('/sell', [FutureOrderController::class, 'sellOrderList'])->name('sell.list');
        Route::get('/cancel-order', [FutureOrderController::class, 'cancelOrder'])->name('cancel');
    });

    // Trades
    Route::prefix('trade')->as('trade.')->group(function () {
        Route::get('/get-trade-modal-data', [FutureTradeController::class, 'getTrade'])->name('get-modal-trade');
        Route::get('/trade', [FutureTradeController::class, 'list'])->name('list');
    });

    // Active Positions
    Route::prefix('position')->as('position.')->group(function () {
        Route::get('/get-positions-modal-data', [FuturePositionsController::class, 'getPositions'])->name('get-modal-position');
        Route::get('/positions', [FuturePositionsController::class, 'list'])->name('list');

        // history
        Route::get('/get-position-history-modal-data', [FuturePositionHistoryController::class, 'getPositionHistory'])->name('get-modal-position-history');
        Route::get('/position-histories', [FuturePositionHistoryController::class, 'list'])->name('history.list');
    });

    // Settings
    Route::get('/settings', [FutureTradeSettingController::class, 'index'])->name('settings');
    Route::post('/settings', [FutureTradeSettingController::class, 'save'])->name('settings.save');

});
