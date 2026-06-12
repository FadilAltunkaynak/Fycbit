<?php

use App\Validators\OrderDataValidators\FeesZeroValidator;
use App\Validators\OrderDataValidators\MarketFeesZeroValidator;
use App\Validators\OrderDataValidators\OppositeOrderValidator;
use App\Validators\OrderDataValidators\ToleranceValidator;
use App\Validators\OrderDataValidators\WalletBalanceValidator;

return [

    'validators' => [
        OppositeOrderValidator::class,

        // FeesZeroValidator::class,
        /* This validator validates depending on a function calculated_fee_limit()
        this function uses a trade fee system which is no longer available on TradexPro.
        Commenting out this validator reduces 4 DB queries for every Limit buy and sell order. */

        MarketFeesZeroValidator::class,
        ToleranceValidator::class,
        WalletBalanceValidator::class,
    ]

];