<?php

namespace App\Services\FundTransferService\GetWalletBalance;

use App\Model\Wallet;
use App\Services\FundTransferService\DataObject\GetWalletBalanceParams;

class SpotWalletBalance
{
    /**
     * Get Spot Wallet Balance
     * 
     * @param GetWalletBalanceParams $params
     * @return float|string
     */
    public function __invoke(GetWalletBalanceParams $params): float|string
    {
        $query = Wallet::where('user_id', $params->user_id)
            ->where('coin_type', $params->coin_type);

        $wallet = $query->first();

        return $wallet?->balance ?? 0;
    }
}
