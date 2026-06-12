<?php

namespace Modules\FutureTrade\Repositories\TradeRepository;

use Modules\FutureTrade\DataObject\FutureTradeData;
use Modules\FutureTrade\Entities\FutureTrade;
use Modules\FutureTrade\Http\Requests\Api\FutureTradeHistoryFilterRequest;

interface IFutureTradeRepository
{
    /**
     * Create New Future Trade
     */
    public static function createTrade(FutureTradeData $tradeData): FutureTrade;

    /**
     * Get Future Trade By User ID
     */
    public static function getLastTrade(int $coin_id): ?FutureTrade;

    public function tradeHistoryFilter(FutureTradeHistoryFilterRequest $request, int $user_id);
}