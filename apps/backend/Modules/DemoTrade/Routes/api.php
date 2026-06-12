<?php

use Illuminate\Http\Request;

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
Route::group(['prefix' => 'demo','namespace'=>'Api\User', 'middleware' => ['auth:api','api-user','last_seen']], function () {
    Route::get('wallet-list','WalletController@walletList');

    Route::get('get-coin-list', 'CoinController@getCoinList');
    Route::post('get-wallet-balance', 'CoinController@getWalletBalance');

    Route::get('get-my-all-orders-app', 'ExchangeController@getMyExchangeOrdersApp');
    Route::get('get-my-trades-app', 'ExchangeController@getMyExchangeTradesApp');

    Route::get('get-exchange-all-orders-app', 'ExchangeController@getExchangeAllOrdersApp');
    Route::get('app-get-pair', 'ExchangeController@appExchangeGetAllPair');
    Route::get('app-dashboard/{pair?}', 'ExchangeController@appExchangeDashboard');
    Route::get('get-exchange-market-trades-app', 'ExchangeController@getExchangeMarketTradesApp');
    Route::get('get-exchange-chart-data-app', 'ExchangeController@getExchangeChartDataApp');

    Route::post('cancel-open-order-app', 'ExchangeController@deleteMyOrderApp');

    Route::post('buy-limit-app', "BuyOrderController@placeBuyLimitOrderApp");
    Route::post('buy-market-app', "BuyOrderController@placeBuyMarketOrderApp");
    Route::post('buy-stop-limit-app', "BuyOrderController@placeBuyStopLimitOrderApp");
    Route::post('sell-limit-app', "SellOrderController@placeSellLimitOrderApp");
    Route::post('sell-market-app', "SellOrderController@placeSellMarketOrderApp");
    Route::post('sell-stop-limit-app', "SellOrderController@placeStopLimitSellOrderApp");
    
});