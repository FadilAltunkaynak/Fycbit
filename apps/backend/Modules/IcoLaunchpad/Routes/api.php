<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Api', 'middleware' => ['checkApi', 'check_ico_module']], function () {

    Route::get('ico-active-list', 'ICOController@getActiveICOList');
    Route::get('ico-details', 'ICOController@getICODetails');

    Route::get('ico-phase-active-list', 'ICOPhaseController@getICOPhaseActiveList');
    Route::get('active-ico-phase-details', 'ICOPhaseController@getActiveICOPhaseDetails');

    Route::get('launchpad-settings', 'LaunchpadController@launchpadSettings');

    Route::group(['middleware' => ['auth:api', 'api-user']], function () {

        Route::post('create-update-ico-token', 'ICOController@storeUpdateICO');
        Route::get('ico-list-user', 'ICOController@userICOList');
        Route::post('get-contract-address-details', 'ICOController@getAddressDettailsApi');

        Route::post('create-update-ico-token-phase', 'ICOPhaseController@storeUpdateICOTokenPhase');
        Route::post('create-update-ico-token-phase-additional', 'ICOPhaseController@storeUpdateICOTokenPhaseAdditional');
        Route::get('ico-token-phase-list', 'ICOPhaseController@getICOTokenPhaseList');
        Route::get('ico-token-phase-details', 'ICOPhaseController@getDetailsOfICOTokenPhase');
        Route::get('ico-token-phase-additional-details', 'ICOPhaseController@getDetailsOfICOPhaseAdditional');
        Route::post('save-ico-phase-status', 'ICOPhaseController@saveICOPhaseStatus');

        Route::get('dynamic-form', 'DynamicFormController@index');
        Route::post('dynamic-form-submit', 'DynamicFormController@submitDynamicForm');
        Route::get('submitted-dynamic-form-list', 'DynamicFormController@submittedDynamicFormList');

        // ICO Token Buy
        Route::get('token-buy-page', 'IcoTokenBuyController@getPageData');
        Route::get('token-buy-history/{type?}', 'IcoTokenBuyController@getTokenBuyHistory');
        Route::get('my-token-balance', 'IcoTokenBuyController@getUserTokenBalanceList');
        Route::get('check-phase', 'IcoTokenBuyController@checkPhase');
        Route::post('token-buy-ico', 'IcoTokenBuyController@makeRequest');
        //new process
        Route::post('token-buy-ico-new', 'IcoTokenBuyController@makeRequestNew');
        Route::get('token-price-info', 'IcoTokenBuyController@getPriceInfo');
        // ICO Token Buy Withdraw
        Route::get('token-earns', 'ICOWithdrawController@getTokenEarnig');
        Route::post('token-withdraw-price', 'ICOWithdrawController@getTokenWithdrawlPrice');
        Route::post('token-withdraw-request', 'ICOWithdrawController@getTokenWithdrawlRequest');
        Route::get('token-withdraw-list', 'ICOWithdrawController@getTokenWithdrawalList');

        //message
        Route::get('ico-chat-details', 'ICOChatController@getICOChatDetails');
        Route::post('ico-chat-conversation-store', 'ICOChatController@getICOChatConversationStore');

        //paystack
        Route::post('get-paystack-payment-url', 'PaystackPaymentController@getPaystackPaymentURL');
        Route::post('verification-paystack-payment', 'PaystackPaymentController@verificationPaystackPayment');
    });
});
