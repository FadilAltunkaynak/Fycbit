<?php

namespace App\Services\FundTransferService\FundChecker;

use App\Services\FundTransferService\DataObject\FundCheckerQueryParams;
use Illuminate\Database\Eloquent\Model;
use Modules\FutureTrade\Entities\FutureWallet;

class FutureFundChecker
{
    /**
     * Check Has Wallet
     * Create Wallet If Not Found
     * Check Wallet Balance , return null on empty balance
     * return Wallet Model
     *
     * @param \App\Services\FundTransferService\DataObject\FundCheckerQueryParams $params
     * @return Model
     */
    public function __invoke(FundCheckerQueryParams $params): Model|null
    {
        $wallet = FutureWallet::where('user_id', $params->user_id)
            ->where('coin_id', $params->coin?->id)
            ->first();

        if(!$wallet){
            try{
                $wallet = FutureWallet::create([
                    'user_id' => $params->user_id,
                    'coin_id' => $params->coin?->id,
                    'name'    => $params->coin?->coin_type . ' wallet',
                  'coin_type' => $params->coin?->coin_type
                ]);
            } catch (\Exception $e) {
                storeException("Future Fund Checker",$e->getLine());
                storeException("Future Fund Checker",$e->getMessage());
                throw new \Exception(__("Fund wallet not found"));
            }
        }

        if(!$wallet)
        throw new \Exception(__("Fund wallet not found"));

        if(!$wallet?->balance && $params->from)
        throw new \Exception(__("Insufficient fund balance"));

        return $wallet;
    }
}
