<?php
namespace App\Services\FundTransferService\DataObject;

class GetWalletBalanceParams
{
    public function __construct(
        public int $user_id,
        public string $coin_type,
    ) {
    }
}
