<?php

use Illuminate\Support\Facades\Route;
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

Route::group(['prefix' => 'dynamicform', 'group' => 'dynamic_form_settings', 'middleware' => ['auth', 'admin', 'default_lang','permission', 'check_ico_module']], function () {
    Route::get('/', 'DynamicFormController@index')->name('dynamicFormCreateICO');
    Route::get('dashboard','DashboardController@dashboardICO')->name('dashboardICO');
    Route::get('submitted-form-ico', 'DynamicFormController@submittedFormICO')->name('submitted-form-ico');
    Route::get('submitted-form-details-{form_id}', 'DynamicFormController@submittedFormDetails')->name('submitted-form-details');

    Route::get('setup-dynamic-form', 'DynamicFormController@setupDynamicForm')->name('setupDynamicForm');

    // Ico
    Route::get('ico-list', 'ICOController@listICO')->name('listICO');
    Route::get('add-new-ico', 'ICOController@addNewICO')->name('addNewICO');
    Route::get('edit-ico/{id}', 'ICOController@editICO')->name('editICO');
    Route::get('ico-translation-page/{id}', 'ICOController@translationListICO')->name('translationListICO');
    Route::get('ico-translation-update-page/{id}/{lan_key}', 'ICOController@tokenTranslateUpdatePage')->name('tokenTranslateUpdatePage');
    Route::post('ico-translation-update', 'ICOController@updateLanguageTextToken')->name('updateLanguageTextToken');
    // Ico token buy
    Route::get('token-buy-list', 'ICOTokenBuyController@tokenList')->name("buyTokenList");
    Route::get('token-buy-pending', 'ICOTokenBuyController@getPendingList')->name("buyTokenPending");
    Route::get('token-buy-reject', 'ICOTokenBuyController@getRejectedList')->name("buyTokenReject");
    Route::get('token-buy-active', 'ICOTokenBuyController@getActiveList')->name("buyTokenActive");

    Route::post('get-contract-address-details', 'ICOController@getAddressDettailsApi')->name('getAddressDettailsApi');

    Route::get('ico-phase-list/{icoTokenId}', 'ICOPhaseController@listICOPhase')->name('listICOPhase');
    Route::get('create-ico-phase/{icoTokenId}', 'ICOPhaseController@addNewICOPhase')->name('addNewICOPhase');
    Route::get('edit-ico-phase/{icoPhaseId}', 'ICOPhaseController@editICOPhase')->name('editICOPhase');


    Route::get('launchpad-page-settings', 'LaunchpadController@launchpadPageSettings')->name('launchpadPageSettings');

    Route::get('settings', 'SettingsController@index')->name('icoSettings');
    Route::post('settings-save', 'SettingsController@settingsSave')->name('icoSettingsSave');
    Route::get('launchpad-feature-list', 'LaunchpadController@launchpadFeatureList')->name('launchpadFeatureList');
    Route::get('launchpad-feature-settings', 'LaunchpadController@launchpadFeatureSettings')->name('launchpadFeatureSettings');
    Route::get('launchpad-feature-settings-edit/{id}', 'LaunchpadController@launchpadFeatureSettingsEdit')->name('launchpadFeatureSettingsEdit');

    Route::get('update-request-table-info/{type}/{id}', 'UpdateRequestController@updateRequestTableInfo')->name('updateRequestTableInfo');


    Route::get('ico-chat-details/{id}', 'ICOChatController@getICOChatDetails')->name('getICOChatDetails');
    Route::post('ico-chat-conversation-store', 'ICOChatController@getICOChatConversationStore')->name('getICOChatConversationStore');

    // ico payment method
    Route::get('ico-payment-method', 'ICOController@paymentMethodList')->name('IcoPaymentMethod');
    Route::get('ico-payment-method-add', 'ICOController@paymentMethodAdd')->name('IcoPaymentMethodAdd');
    Route::get('ico-payment-method-edit-{id}', 'ICOController@paymentMethodAddEdit')->name('paymentMethodAddEdit');

    //withdraw
    Route::get('ico-withdraw-history-list', 'ICOWithdrawController@getICOWithdrawhistoryList')->name('getICOWithdrawhistoryList');
    Route::get('ico-withdraw-pending-list', 'ICOWithdrawController@getICOWithdrawPendingList')->name('getICOWithdrawPendingList');
    Route::get('ico-withdraw-accept-list', 'ICOWithdrawController@getICOWithdrawAcceptList')->name('getICOWithdrawAcceptList');
    Route::get('ico-withdraw-reject-list', 'ICOWithdrawController@getICOWithdrawRejectList')->name('getICOWithdrawRejectList');

    Route::group(['group' => 'dynamic_form_settings','middleware' => 'check_demo'], function () {
        Route::get('ico-payment-method-delete-{id}', 'ICOController@paymentMethodAddDelete')->name('paymentMethodAddDelete');
        Route::post('ico-payment-method-status', 'ICOController@paymentMethodStatus')->name('paymentMethodAddStatus');
        Route::post('ico-payment-method-add', 'ICOController@paymentMethodAddProccess')->name('IcoPaymentMethodAddProccess');
        Route::post('launchpad-feature-settings-save', 'LaunchpadController@launchpadFeatureSettingsSave')->name('launchpadFeatureSettingsSave');
        Route::post('launchpad-feature-status', 'LaunchpadController@launchpadFeatureStatus')->name('launchpadFeatureStatus');
        Route::post('launchpad-page-settings-update', 'LaunchpadController@launchpadPageSettingsUpdate')->name('launchpadPageSettingsUpdate');
        Route::post('save-ico-phase', 'ICOPhaseController@saveICOPhase')->name('saveICOPhase');
        Route::post('save-ico-phase-featured', 'ICOPhaseController@saveICOPhaseFeatured')->name('saveICOPhaseFeatured');
        Route::post('save-ico-phase-status', 'ICOPhaseController@saveICOPhaseStatus')->name('saveICOPhaseStatus');
        Route::post('accepted-ico-token', 'ICOController@acceptedICOToken')->name('acceptedICOToken');
        Route::post('modification-ico-token', 'ICOController@modificationICOToken')->name('modificationICOToken');
        Route::post('rejected-ico-token', 'ICOController@rejectedICOToken')->name('rejectedICOToken');
        Route::get('delete-ico-phase/{icoPhaseId}', 'ICOPhaseController@deleteICOPhase')->name('deleteICOPhase');
        Route::get('ico-phase-additional-delete/{id}', 'ICOPhaseController@deleteICOPhaseAdditionalInfo')->name('deleteICOPhaseAdditionalInfo');
        Route::get('update-request-table-info-accept/{id}', 'UpdateRequestController@updateRequestTableInfoAccept')->name('updateRequestTableInfoAccept');
        Route::get('update-request-table-info-reject/{id}', 'UpdateRequestController@updateRequestTableInfoReject')->name('updateRequestTableInfoReject');
        Route::get('launchpad-feature-settings-delete/{id}', 'LaunchpadController@launchpadFeatureSettingsDelete')->name('launchpadFeatureSettingsDelete');
        Route::get('token-buy-accept-{id}', 'ICOTokenBuyController@acceptToken')->name("buyTokenAccept");
        Route::get('ico-token-buy-reject-{id}', 'ICOTokenBuyController@rejectToken')->name("icoBuyTokenReject");
        Route::get('delete-ico/{id}', 'ICOController@deleteICO')->name('deleteICO');
        Route::post('store-update-new-ico', 'ICOController@storeUpdateICO')->name('storeUpdateICO');
        Route::post('ico-featured-status-change', 'ICOController@icoFeaturedStatusChange')->name('icoFeaturedStatusChange');
        Route::post('ico-status-change', 'ICOController@icoStatusChange')->name('icoStatusChange');
        Route::post('setup-dynamic-form', 'DynamicFormController@setupDynamicFormSave')->name('setupDynamicFormSave');
        Route::post('accepted-submitted-form-ico', 'DynamicFormController@accpetedSubmittedFormICO')->name('accpetedSubmittedFormICO');
        Route::post('rejected-submitted-form-ico', 'DynamicFormController@rejectedSubmittedFormICO')->name('rejectedSubmittedFormICO');
        Route::post('store', 'DynamicFormController@store')->name('store');
        Route::get('ico-withdraw-accept-request/{id}', 'ICOWithdrawController@IcoWithdrawAcceptRequest')->name('IcoWithdrawAcceptRequest');
        Route::get('ico-withdraw-reject-request/{id}', 'ICOWithdrawController@IcoWithdrawRejectRequest')->name('IcoWithdrawRejectRequest');
        Route::post('ico-fiat-withdraw-accept-request', 'ICOWithdrawController@IcoFiatWithdrawAcceptRequest')->name('IcoFiatWithdrawAcceptRequest');
    });
});
