<?php

namespace App\Services\FundTransferService\CoinGetQuery;

use App\Model\Coin;
use App\Services\FundTransferService\DataObject\FundTransferQueryParams;

class SpotCoinGetQuery
{
    public function __invoke(FundTransferQueryParams $params)
    {
        return Coin::join('wallets', 'wallets.coin_id', '=', 'coins.id')
            ->where('wallets.user_id', $params->user_id)
            ->where('wallets.balance', '<>', 0)
            ->where('coins.status', STATUS_ACTIVE)
            ->select([
                'coins.id',
                'wallets.balance',
                'coins.coin_type',
                'coins.coin_icon',
            ])
            ->orderBy("wallets.$params->orderBy", $params->direction)
            ->get();
    }
}