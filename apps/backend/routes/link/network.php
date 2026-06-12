<?php

use App\Http\Controllers\admin\CoinNetworkController;
use Illuminate\Support\Facades\Route;

// Networks
Route::group(['middleware' => 'check_demo', 'group' => 'network'], function () {
    Route::get('network', 'NetworkController@getNetworkList')->name('getNetworkList');
    Route::get('create-network/{id?}', 'NetworkController@createNetwork')->name('createNetwork');
    Route::post('check-current-block', 'NetworkController@checkCurrentBlock')->name('checkCurrentBlock');
    Route::get('networks-by-coin-provider/{coin_id}', 'NetworkController@networksByCoinProvider')->name('networksByCoinProvider');

    Route::group(['middleware' => 'check_demo'], function () {
        Route::post('network', 'NetworkController@createNetworkProcess')->name('createNetworkProcess');
        Route::post('network-status-change', 'NetworkController@changeNetworkStatus')->name('changeNetworkStatus');
        // Route::get('network-delete-{id}', 'NetworkController@deleteNetwork')->name('deleteNetwork');
    });
});

// Coin Network
Route::group(['middleware' => 'check_demo', 'group' => 'coin_network'], function () {
    Route::get('coin-network', 'CoinNetworkController@getCoinNetworkList')->name('getCoinNetworkList');
    Route::get('create-coin-network/{id?}', 'CoinNetworkController@createCoinNetwork')->name('createCoinNetwork');
    Route::get('edit-coin-network/{id?}', 'CoinNetworkController@updateCoinNetwork')->name('updateCoinNetwork');
    Route::get('coin-network-settings/{id}', 'CoinNetworkController@coinNetworkSettings')->name('adminCoinSettings');

    Route::group(['middleware' => 'check_demo'], function () {
        Route::post('coin-network', 'CoinNetworkController@createCoinNetworkProcess')->name('createCoinNetworkProcess');
        Route::post('update-coin-network', [CoinNetworkController::class, 'updateCoinNetworkProcess'])->name('updateCoinNetworkProcess');
        Route::post('coin-network-status-change', 'CoinNetworkController@changeCoinNetworkStatus')->name('changeCoinNetworkStatus');
        Route::get('coin-network-delete-{id}', 'CoinNetworkController@coinNetworkDelete')->name('coinNetworkDelete');
    });
});

// System wallet
Route::group(['middleware' => 'check_demo', 'group' => 'system_wallet'], function () {
    Route::get('system-wallet', 'SystemWalletController@getSystemWalletList')->name('getSystemWalletList');
    Route::get('create-system-wallet/{id?}', 'SystemWalletController@createSystemWallet')->name('createSystemWallet');

    Route::group(['middleware' => 'check_demo'], function () {
        Route::post('system-wallet', 'SystemWalletController@createSystemWalletProccess')->name('createSystemWalletProccess');
        Route::post('system-wallet-status-change', 'SystemWalletController@changeSystemWalletStatus')->name('changeSystemWalletStatus');
        Route::post('system-wallet-key-view', 'SystemWalletController@viewSystemWalletey')->name('viewSystemWalletey');
        Route::post('system-wallet-key-update', 'SystemWalletController@updateSystemWalletey')->name('updateSystemWalletey');
        Route::post('system-wallet-check-address', 'SystemWalletController@systemWalletCheckAddress')->name('systemWalletCheckAddress');
    });
});

Route::group(['group' => 'supported_network'], function () {
    Route::get("create-supported-network", "supportedNetworkController@creteSupportedNetworkPage")->name("creteSupportedNetworkPage");
    Route::post("create-supported-network", "supportedNetworkController@creteSupportedNetwork")->name("creteSupportedNetwork");
    Route::post("edit-supported-network", "supportedNetworkController@editSupportedNetwork")->name("editSupportedNetwork");
    Route::get("supported-network-list", "supportedNetworkController@supportedNetwork")->name("supportedNetworkList");
    Route::get("supported-network-edit-{id}", "supportedNetworkController@supportedNetworkEdit")->name("supportedNetworkEdit");
    Route::group(['middleware' => 'check_demo'], function () {
        Route::post('supported-network-status', 'supportedNetworkController@supportedNetworkStatus')->name('supportedNetworkStatus');
        Route::post('supported-network-edit', 'supportedNetworkController@supportedNetworkEditProccess')->name('supportedNetworkEditProccess');
    });
});
