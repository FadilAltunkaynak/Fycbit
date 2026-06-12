<?php

namespace App\Services\FundTransferService\DataObject;

use App\Model\Coin;

class FundCheckerQueryParams
{
    public function __construct(
        public int $user_id,
        public Coin $coin,
        public bool $from = false, // From wallet
        public bool $to = false,   // To wallet
    ) {}
}