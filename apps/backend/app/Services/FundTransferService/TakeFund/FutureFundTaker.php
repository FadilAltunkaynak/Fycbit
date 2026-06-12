<?php

namespace App\Services\FundTransferService\TakeFund;

use App\Services\FundTransferService\DataObject\FundManageParams;
use DB;
use Modules\FutureTrade\Emum\PositionStatusEnum;
use Modules\FutureTrade\Entities\FutureBuy;
use Modules\FutureTrade\Entities\FuturePosition;
use Modules\FutureTrade\Entities\FutureSell;
use Modules\FutureTrade\Entities\FutureWallet;

class FutureFundTaker
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

        $hasOpenPosition = FuturePosition::query()
            ->where('user_id', $params->user_id)
            ->where('status', PositionStatusEnum::OPEN->value)
            ->exists();

        $hasOpenOrder = FutureBuy::query()
            ->where('user_id', $params->user_id)
            ->pendingOrder()
            ->exists()
            || FutureSell::query()
                ->where('user_id', $params->user_id)
                ->pendingOrder()
                ->exists();

        if ($hasOpenPosition || $hasOpenOrder) {
            throw new \Exception(__('You have open future positions or orders. Please close them before transferring funds.'));
        }

        $wallet = FutureWallet::where('user_id', $params->user_id)
            ->where('id', $params->wallet_id)
            ->lockForUpdate()
            ->first();

        if(!$wallet){
            DB::rollBack();
            throw new \Exception(__("Fund wallet not found to take fund"));
        }

        // check balance has enough amount
        if(bccompx($wallet->balance , $params->amount) == -1) // balance < amount
            throw new \Exception(__('Insufficient from fund balance'));

        // decrement balance here
        $response = $wallet->decrement('balance', $params->amount);
        return !!$response;
    }
}
