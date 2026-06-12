<?php

namespace App\Services\FundTransferService\TakeFund;

use App\Model\Wallet;
use App\Services\FundTransferService\DataObject\FundManageParams;
use DB;

class SpotFundTaker
{
    /**
     * Highly recommended to use this method in db transaction ( used lockForUpdate )
     * 
     * Increment amount from wallet
     * 
     * @param \App\Services\FundTransferService\DataObject\FundManageParams $params
     * @return bool
     */
    public function __invoke(FundManageParams $params): bool
    {
        // check amount is numeric
        if (!is_numeric($params->amount)) {
            throw new \Exception(__("Invalid amount"));
        }

        $wallet = Wallet::where('user_id', $params->user_id)
            ->where('id', $params->wallet_id)
            ->lockForUpdate()
            ->first();

        if(!$wallet){
            DB::rollBack();
            throw new \Exception(__("Fund wallet not found to take fund"));
        }

        // check balance has enough amount
        if(bccompx($wallet->balance , $params->amount) == -1) { // balance < amount
            throw new \Exception(__('Insufficient from fund balance'));
        }

        // decrement balance here
        $response = $wallet->decrement('balance', $params->amount);
        return !!$response;
    }
}
