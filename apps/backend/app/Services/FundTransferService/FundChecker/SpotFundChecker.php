<?php

namespace App\Services\FundTransferService\FundChecker;

use App\Model\Wallet;
use App\Services\FundTransferService\DataObject\FundCheckerQueryParams;
use Illuminate\Database\Eloquent\Model;

class SpotFundChecker
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
    public function __invoke(FundCheckerQueryParams $params): Model
    {
        $wallet = Wallet::where('user_id', $params->user_id)
            ->where('coin_id', $params->coin?->id)
            ->first();

        if(!$wallet){
            try{
                $wallet = Wallet::create([
                    'user_id' => $params->user_id,
                    'coin_id' => $params->coin?->id,
                    'name'    => $params->coin?->coin_type . ' wallet',
                  'coin_type' => $params->coin?->coin_type
                ]);
            } catch (\Exception $e) {
                storeException("Spot Fund Checker",$e->getLine());
                storeException("Spot Fund Checker",$e->getMessage());
                throw new \Exception(__("Fund wallet not found"));
            }
        }

        if(!$wallet) {
            throw new \Exception(__("Fund wallet not found"));
        }

        if(!$wallet?->balance && $params->from) {
            throw new \Exception(__("Insufficient fund balance"));
        }

        return $wallet;
    }
}
