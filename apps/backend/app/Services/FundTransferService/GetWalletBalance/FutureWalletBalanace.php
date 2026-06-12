<?php

namespace App\Services\FundTransferService\GetWalletBalance;

use App\Model\Coin;
use App\Model\Wallet;
use App\Services\FundTransferService\DataObject\GetWalletBalanceParams;
use Modules\FutureTrade\Entities\FutureWallet;
use Modules\FutureTrade\Services\WalletServices\WalletService;

class FutureWalletBalanace
{
    /**
     * Get Future Wallet Balance
     * 
     * @param GetWalletBalanceParams $params
     * @return float|string
     */
    public function __invoke(GetWalletBalanceParams $params): float|string
    {
        $coin = Coin::where('coin_type', $params->coin_type)->first();
        $query = FutureWallet::where('user_id', $params->user_id)
            ->where('coin_id', $coin?->id ?: 0);

        $wallet = $query->first();

        $availableBalance = app(WalletService::class)->getAvailableBalance($coin?->id ?: 0, $params->user_id);

        return $availableBalance;
    }
}
