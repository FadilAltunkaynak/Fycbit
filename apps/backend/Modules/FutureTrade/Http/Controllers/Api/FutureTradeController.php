<?php

namespace Modules\FutureTrade\Http\Controllers\Api;

use App\Facades\ResponseFacade;
use Illuminate\Routing\Controller;
use Modules\FutureTrade\Http\Requests\Api\FutureTradeHistoryFilterRequest;
use Modules\FutureTrade\Services\TradeServices\TradeService;

class FutureTradeController extends Controller
{
    public function __construct(
        private TradeService $tradeService
    ){}

    public function getMarketTradeList(string $coin_pair_uid): mixed
    {
        $response = $this->tradeService->marketTradeList($coin_pair_uid);
        return ResponseFacade::success($response)->send();
    }

    public function getTradeHistory(FutureTradeHistoryFilterRequest $request): mixed
    {
        $response = $this->tradeService->tradeHistoryFilter($request);
        return ResponseFacade::success($response)->send();
    }
}
