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

Route::group(['group' => 'Addon', 'prefix' => 'p2p', 'middleware'=> ['auth', 'admin','permission:P2P_Addon']], function () {
    Route::get('/dashboard', 'DashboardController@dashboard')->name('p2pDashboard');

    // Payment Time
    Route::get('/payment-time', 'PaymentTimeController@paymentTime')->name('p2pPaymentTime');
    Route::get('/payment-time-create/{uid?}', 'PaymentTimeController@paymentTimeCreatePage')->name('p2pPaymentTimeCreatePage');
    Route::post('/payment-time-delete', 'PaymentTimeController@paymentTimeDeletePage')->name('p2pPaymentTimeDeletePage');
    Route::post('/payment-time-create', 'PaymentTimeController@paymentTimeCreate')->name('p2pPaymentTimeCreate');

    // Coin Settings
    Route::get('coin-list','CoinController@coinList')->name('p2pCoinList');
    Route::get('coin-edit-{coin_type}','CoinController@coinEdit')->name('p2pCoinEdit');
    Route::post('coin-edit','CoinController@coinEditProcess')->name('coinEditProcess');

    // Currency Settings
    Route::get('currency-list','CurrencyController@currencyList')->name('p2pCurrencyList');
    Route::get('currency-edit-{code}','CurrencyController@currencyEdit')->name('p2pCurrencyEdit');
    Route::post('currency-edit','CurrencyController@currencyEditProcess')->name('currencyEditProcess');

    //Payment Method Settings
    Route::get('payment-methods-list','PaymentMethodController@paymentMethodList')->name('paymentMethodList');
    Route::get('payment-methods-create/{uid?}','PaymentMethodController@paymentMethodCreate')->name('p2pPaymentMethodCreate');
    Route::post('payment-methods-delete','PaymentMethodController@paymentMethodDelete')->name('paymentMethodDelete');
    Route::post('payment-methods-create','PaymentMethodController@paymentMethodCreateProcess')->name('paymentMethodCreateProcess');

    // Setting
    Route::get('setting','SettingController@settingPage')->name('p2pSettingPage');
    Route::post('setting','SettingController@settingUpdate')->name('p2pSettingUpdate');

    // Buy / Sell ads list with filters
    Route::get('ads-list','AdsController@adsListPage')->name('adsListPage');
    Route::get('ads-buy-list','AdsController@adsBuyList')->name('adsBuyList');
    Route::get('ads-sell-list','AdsController@adsSellList')->name('adsSellList');

    //dispute list
    Route::get('dispute-list', 'TradeController@getDisputedList')->name('getDisputedList');
    Route::get('dispute-details-{uid}', 'TradeController@getDisputeDetails')->name('getDisputeDetails');
    Route::post('dispute-assign-to', 'TradeController@assignDisputeDetails')->name('assignDisputeDetails');
    Route::post('dispute-refund', 'TradeController@refundDisputeDetails')->name('refundDisputeDetails');
    Route::post('dispute-release', 'TradeController@releaseDisputeDetails')->name('releaseDisputeDetails');


    //trade history
    Route::get('order-list-{status}', 'TradeController@getOrderList')->name('getOrderList');
    Route::get('order-details-{uid}', 'TradeController@getOrderDetails')->name('getOrderDetails');
    Route::get('user-trade-details-{user_id}', 'TradeController@getUserTradeDetails')->name('getUserTradeDetails');

    //
    Route::get('landing-settings-header',"LandingSettingsController@getLandingSettings")->name('landingSettingsP2p');
    Route::post('landing-settings-header',"LandingSettingsController@setLandingSettings")->name('setLandingSettingsP2p');
    Route::get('landing-settings-how-to',"LandingSettingsController@getLandingHowToP2p")->name('landingHowToP2p');
    Route::post('landing-settings-how-to-save',"LandingSettingsController@setLandingHowToP2p")->name('setLandingHowToP2p');
    Route::get('landing-settings-how-to-data',"LandingSettingsController@getLandingHowToP2pData")->name('landingHowToDataP2p');
    Route::get('landing-settings-how-to-add',"LandingSettingsController@getLandingHowToP2pAdd")->name('landingHowToAddP2p');
    Route::get('landing-settings-advantage',"LandingSettingsController@landingAdvantageP2p")->name('landingAdvantageP2p');

    //message
    Route::post('send-message', 'ConversationController@sendMessage')->name('p2pSendMessage');

    //gift card
    Route::get('gift-card-dispute-list', 'GiftCardController@disputeGiftCardList')->name('disputeGiftCardList');
    Route::get('gift-card-dispute-details-{uid}', 'GiftCardController@disputeGiftCardDetails')->name('disputeGiftCardDetails');
    Route::post('gift-card-send-message', 'GiftCardController@giftCardSendMessage')->name('giftCardSendMessage');
    Route::post('gift-card-dispute-refund', 'GiftCardController@refundDisputeDetails')->name('giftCardRefundDisputeDetails');
    Route::post('gift-card-dispute-release', 'GiftCardController@releaseDisputeDetails')->name('giftCardReleaseDisputeDetails');
    Route::post('gift-card-dispute-assign-to', 'GiftCardController@giftCardaAssignDisputeDetails')->name('giftCardaAssignDisputeDetails');

    // gift card ads history
    Route::get('get-gift-card-ads-history', 'GiftCardController@getGiftCardAdsHistory')->name('getGiftCardAdsHistory');
    // gift card order history
    Route::get('get-gift-card-order-history', 'GiftCardController@getGiftCardOrderHistory')->name('getGiftCardOrderHistory');
    Route::get('get-gift-card-details', 'GiftCardController@getGiftCardDetailsAtOrderHistory')->name('getGiftCardDetailsAtOrderHistory');
    // gift card header
    Route::get('gift-card-header', 'GiftCardController@getGiftHeader')->name('getGiftHeader');
    Route::post('gift-card-header', 'GiftCardController@saveGetGiftHeader')->name('saveGetGiftHeader');

    // coin status change api
    Route::post("coin-status-change", 'CoinController@coinStatusChange')->name("coinStatusChange");

    Route::get("fetch-new-coins", "CoinController@fetchNewCoins")->name("fetchNewCoins");
    Route::get("fetch-new-currency", "CurrencyController@fetchNewCurrency")->name("fetchNewCurrency");
});
