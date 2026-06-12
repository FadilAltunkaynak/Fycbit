<?php

namespace Modules\FutureTrade\Repositories\WalletRepository;

use Modules\FutureTrade\Entities\FutureWallet;

interface IFutureWalletRepository
{
    /**
     * Create New Future Wallet
     */
    public static function createWallet(int $coin_id, ?int $userID): FutureWallet;

    /**
     * Get Future Wallet By User ID
     */
    public static function getWallet(int $coin_id, ?int $userID): FutureWallet;
}