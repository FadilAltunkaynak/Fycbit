<?php

namespace App\Services\FundTransferService\GiverFund;

use App\Model\Wallet;
use App\Services\FundTransferService\DataObject\FundManageParams;
use Illuminate\Support\Facades\DB;
use Modules\FutureTrade\Entities\FutureWallet;

class FutureFundGiver
{
    /**
     * Highly recommended to use this method in db transaction ( used lockForUpdate )
     * 
     * Deduct amount from wallet
     * 
     * @param \App\Services\FundTransferService\DataObject\FundManageParams $params
     * @return bool
     */
    public function __invoke(FundManageParams $params): bool
    {
        // check amount is numeric
        if(!is_numeric($params->amount))
        throw new \Exception(__("Invalid amount"));

        $wallet = FutureWallet::where('user_id', $params->user_id)
            ->where('id', $params->wallet_id)
            ->lockForUpdate()
            ->first();

        if(!$wallet){
            DB::rollBack();
            throw new \Exception(__("Fund wallet not found to give fund"));
        }

        // decrement balance here
        $response = $wallet->increment('balance', $params->amount);
        return !!$response;
    }
}
