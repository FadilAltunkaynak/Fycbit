<?php

namespace Modules\P2P\Services;

use App\Model\Wallet;
use Illuminate\Database\Eloquent\Model;
use Modules\P2P\Entities\P2PWallet;

class WalletFinderService
{
    public function findReceiverWallet(
        int $userId, 
        string $coinType,
        string $transactionType
    ): ?Model
    {
        if($transactionType == P2PWallet::WALLET_BALANCE_TRANSFER_RECEIVE) 
        {
            return P2PWallet::where(["user_id" => $userId, "coin_type" => $coinType])
                ->first();
        }

        return Wallet::where(["user_id" => $userId, "coin_type" => $coinType])->first();
    }

    public function findAndLockSenderWalletById(   
        int $walletId,
        string $transactionType
    ): ?Model
    {
        if($transactionType == P2PWallet::WALLET_BALANCE_TRANSFER_RECEIVE) 
        {
            return Wallet::where(["id" => $walletId])
                ->lockForUpdate()
                ->first();
        }

        return P2PWallet::where(["id" => $walletId])
            ->lockForUpdate()
            ->first();
    }
}
