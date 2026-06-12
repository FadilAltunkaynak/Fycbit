<?php

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

Route::group(['group' => 'coin_pair','prefix' => 'admin/demo','middleware' => ['auth', 'admin', 'permission', 'default_lang']], function () {
    Route::get('coins', 'CoinController@coinList')->name('demoCoinList');
    Route::get('coin-edit-{coin_type}','CoinController@coinEdit')->name('demoCoinEdit');
    Route::post('coin-edit','CoinController@coinEditProcess')->name('demoCoinEditProcess');

    Route::get('coin-pairs', 'CoinController@coinPairs')->name('demoCoinPairs');
    Route::get('coin-pairs-chart-update/{id}', 'CoinController@coinPairsChartUpdate')->name('demoCoinPairsChartUpdate');

    Route::get('all-buy-orders-history', 'ReportController@adminAllOrdersHistoryBuy')->name('demoAllOrdersHistoryBuy');
    Route::get('all-sell-orders-history', 'ReportController@adminAllOrdersHistorySell')->name('demoAllOrdersHistorySell');
    Route::get('all-stop-limit-orders-history', 'ReportController@adminAllOrdersHistoryStopLimit')->name('demoAllOrdersHistoryStopLimit');
    Route::get('all-transaction-history', 'ReportController@adminAllTransactionHistory')->name('demoAllTransactionHistory');
    Route::get('clear-history-{type}', 'ReportController@adminClearAllHistory')->name('adminClearAllHistory');
});
Route::group(['middleware' => ['check_demo','auth', 'admin', 'permission', 'default_lang'],'group' => 'coin_pair','prefix' => 'demo'], function () {
    Route::get('coin-pairs-delete/{id}', 'CoinController@coinPairsDelete')->name('demoCoinPairsDelete');
    Route::post('save-coin-pair', 'CoinController@saveCoinPairSettings')->name('demoSaveCoinPairSettings');
    Route::post('change-coin-pair-status', 'CoinController@changeCoinPairStatus')->name('demoChangeCoinPairStatus');
    Route::post('change-coin-pair-bot-status', 'CoinController@changeCoinPairBotStatus')->name('demoChangeCoinPairBotStatus');
});
