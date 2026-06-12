<?php

use App\Enums\FundEnum;
use App\Services\FundTransferService\CoinGetQuery\FutureCoinGetQuery;
use App\Services\FundTransferService\CoinGetQuery\SpotCoinGetQuery;
use App\Services\FundTransferService\FundChecker\FutureFundChecker;
use App\Services\FundTransferService\FundChecker\SpotFundChecker;
use App\Services\FundTransferService\GetWalletBalance\FutureWalletBalanace;
use App\Services\FundTransferService\GetWalletBalance\SpotWalletBalance;
use App\Services\FundTransferService\GiverFund\FutureFundGiver;
use App\Services\FundTransferService\GiverFund\SpotFundGiver;
use App\Services\FundTransferService\TakeFund\FutureFundTaker;
use App\Services\FundTransferService\TakeFund\SpotFundTaker;

return [
    /**
     * Get Coin List By Fund For Transfer Fund Page
     * @param \App\Services\FundTransferService\DataObject\FundTransferQueryParams $params
     * @return \Illuminate\Support\Collection<\App\Model\Coin>
     */
    "coin_query" => [
        FundEnum::SPOT->value => SpotCoinGetQuery::class,
        FundEnum::FUTURE->value => FutureCoinGetQuery::class,
    ],

    /**
     * Check Has Wallet
     * Create Wallet If Not Found
     * Check Wallet Balance , return null on empty balance
     * return Wallet Model
     * 
     * @param \App\Services\FundTransferService\DataObject\FundCheckerQueryParams $params
     * @return Model
     */
    "fund_check" => [
        FundEnum::SPOT->value => SpotFundChecker::class,
        FundEnum::FUTURE->value => FutureFundChecker::class,
    ],

    /**
     * Highly recommended to use this method in db transaction ( used lockForUpdate )
     * 
     * Deduct amount from wallet
     * 
     * @param \App\Services\FundTransferService\DataObject\FundManageParams $params
     * @return bool
     */
    "fund_taker" => [
        FundEnum::SPOT->value => SpotFundTaker::class,
        FundEnum::FUTURE->value => FutureFundTaker::class,
    ],

    "fund_giver" => [
        FundEnum::SPOT->value => SpotFundGiver::class,
        FundEnum::FUTURE->value => FutureFundGiver::class,
    ],

    "wallet_balance" => [
        FundEnum::SPOT->value => SpotWalletBalance::class,
        FundEnum::FUTURE->value => FutureWalletBalanace::class,
    ]
];