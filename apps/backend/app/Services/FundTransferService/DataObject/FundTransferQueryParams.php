<?php

namespace App\Services\FundTransferService\DataObject;

class FundTransferQueryParams
{
    public function __construct(
        public int $user_id,
        public string $orderBy,
        public string $direction
    ) {}
}