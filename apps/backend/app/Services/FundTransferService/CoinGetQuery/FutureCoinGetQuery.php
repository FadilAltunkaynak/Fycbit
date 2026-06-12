<?php

namespace App\Services\FundTransferService\CoinGetQuery;

use App\Model\Coin;
use App\Services\FundTransferService\DataObject\FundTransferQueryParams;
use Illuminate\Database\Eloquent\Collection;

class FutureCoinGetQuery
{
    /**
     * Get Coin List By Fund For Transfer Fund Page
     * @param \App\Services\FundTransferService\DataObject\FundTransferQueryParams $params
     * @return \Illuminate\Support\Collection<Coin>
     */
    public function __invoke(FundTransferQueryParams $params): Collection
    {
        return Coin::join('future_wallets', 'future_wallets.coin_id', '=', 'coins.id')
            ->where('future_wallets.user_id', $params->user_id)
            // ->where('future_wallets.balance', '<>', 0)
            ->where('coins.status', STATUS_ACTIVE)
            ->select([
                'coins.id',
                'future_wallets.balance',
                'coins.coin_type',
                'coins.coin_icon',
            ])
            ->orderBy("future_wallets.$params->orderBy", $params->direction)
            ->get();
    }
}