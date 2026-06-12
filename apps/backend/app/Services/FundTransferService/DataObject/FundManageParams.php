<?php

namespace App\Services\FundTransferService\DataObject;

use App\Model\Coin;

class FundManageParams
{
    public function __construct(
        public int $user_id,
        public int $wallet_id,
        public int|float|string $amount,
    ) {}
}