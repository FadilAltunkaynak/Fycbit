<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group(['prefix' => 'p2p','namespace' => 'Api'], function () {
    Route::get('ads-market-setting', 'AdsController@adsMarketSetting');
    Route::post('ads-filter-change', 'AdsController@adsFilterChange');

    //gift card
    Route::get('all-gift-card-ads-list', 'GiftCardController@allGiftCardAdsList');
    Route::get('get-gift-card-trade-header', 'GiftCardController@getGiftCardTradeHeader');
});
Route::group(['prefix' => 'p2p','namespace' => 'Api','middleware' => ['auth:api']], function () {
    Route::get('ads-create-setting', 'AdsController@adsCreateSetting');
    Route::post('ads', 'AdsController@adsCreate');
    Route::post('ads-available-balance', 'AdsController@availableBalance');
    Route::get('ads-price-get', 'AdsController@adsPriceGet');
    Route::post('ads-status-change', 'AdsController@adsStatusChange');
    Route::post('user-ads-filter', 'AdsController@userAdsFilterChange');
    Route::get('ads-details', 'AdsController@adsDetails');
    Route::post('ads-edit', 'AdsController@adsEdit');
    Route::post('ads-delete', 'AdsController@adsDelete');
    Route::post('my-ads-details', 'AdsController@myAdsDetails');
    // market price get
    Route::post('get-market-price', 'AdsController@getMarketPrice');
    //get wallet for user
    Route::get('wallets', 'WalletController@walletList');
    Route::get('wallet-details', 'WalletController@walletDetails');
    Route::post('transfer-wallet-balance', 'WalletController@walletBlanceTransfer');
    // payment method add
    Route::get('admin-payment-method', 'PaymentMethodController@adminPaymentMethod');
    Route::post('payment-method', 'PaymentMethodController@createPaymentMethod');
    Route::get('payment-method-delete-{id}', 'PaymentMethodController@deletePaymentMethod');
    Route::get('payment-method', 'PaymentMethodController@getPaymentMethod');
    Route::get('details-payment-method-{uid}', 'PaymentMethodController@getPaymentMethodDetails');
    Route::get('payment-record', 'PaymentMethodController@getUserPaymentRecord');

    // p2p trade
    Route::post('get-p2p-order-rate','P2pTradeController@getP2pOrderRate');
    Route::post('place-p2p-order','P2pTradeController@placeOrder');
    Route::post('get-p2p-order-details','P2pTradeController@orderDetails');
    Route::post('payment-p2p-order','P2pTradeController@paymentOrder');
    Route::post('release-p2p-order','P2pTradeController@releaseP2pOrder');
    Route::post('cancel-p2p-order','P2pTradeController@cancelP2pOrder');
    Route::get('my-p2p-order','P2pTradeController@myP2pOrder');
    Route::get('my-p2p-dispute','P2pTradeController@myP2pDisputeOrder');
    Route::post('order-feedback','P2pTradeController@orderFeedback');
    Route::get('my-order-list-data','P2pTradeController@myOrderListData');

    //dispute process
    Route::post('dispute-process', 'P2pTradeController@disputeProcess');


    //message
    Route::post('send-message', 'ConversationController@sendMessage');

    // UserInfo
    Route::get('user-center', 'UserController@userCenter');
    Route::get('user-profile', 'UserController@userProfile');

    //gift card
    Route::post('store-gift-card-adds', 'GiftCardController@storeGiftCardAdds');
    Route::post('update-gift-card-adds', 'GiftCardController@updateGiftCardAdds');
    Route::get('gift-card-details' , 'GiftCardController@giftCardDetails');
    Route::get('status-change-gift-card-ads', 'GiftCardController@statusChangeGiftCardAds');
    Route::get('user-gift-card-ads-list', 'GiftCardController@userGiftCardAdsList');
    Route::post('gift-card-delete', 'GiftCardController@giftCardDelete');
    Route::post('place-gift-card-order', 'GiftCardController@placeGiftCardOrder');
    Route::post('pay-now-gift-card-order', 'GiftCardController@payNowGiftCardOrder');
    Route::post('payment-confirm-gift-card-order', 'GiftCardController@paymentConfirmGiftCardOrder');
    Route::post('gift-card-order-cancel', 'GiftCardController@cancelGiftCardOrder');
    Route::post('send-message-gift', 'GiftCardController@sendMessage');
    Route::post('gift-card-order-dispute', 'GiftCardController@disputeOrderProcess');
    Route::get('get-gift-card-page-data', 'GiftCardController@getGiftCardPageData');
    Route::get('get-gift-card-p2p', 'GiftCardController@getGiftCardData');
    Route::get('get-gift-card-ads-details-p2p', 'GiftCardController@getGiftCardAdsDetails');
    Route::get('filter-gift-card-ads', 'GiftCardController@filterGiftCardAds');
    Route::get('get-gift-card-order', 'GiftCardController@getGiftCardOrder');
    Route::get('get-gift-card-orders', 'GiftCardController@getGiftCardOrdersList');
    Route::get('get-gift-card-details-{gift_card_id}', 'GiftCardController@getGiftCardDetails');
    Route::post('update-gift-card-order-feedback', 'GiftCardController@updateGiftCardOrderFeedback');
});

