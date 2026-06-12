<?php

namespace Modules\FutureTrade\Repositories\CoinPairRepository;

use Modules\FutureTrade\DataObject\FutureCoinPairData;
use Modules\FutureTrade\Entities\FutureCoinPair;

interface ICoinPairRepository
{
    /**
     * Get Coin Pair By UID
     */
    public static function getCoinPairByUid(string $uid): ?FutureCoinPair;

    /**
     * Get Coin Pair By ID
     */
    public static function getCoinPairById($id): ?FutureCoinPair;

    /**
     * Insert New Coin Pair Data
     */
    public function insertCoinPair(FutureCoinPairData $data): ?FutureCoinPair;

    /**
     * Update Coin Pair
     */
    public function updateCoinPair(FutureCoinPair $coinPair, FutureCoinPairData $data): ?FutureCoinPair;
}