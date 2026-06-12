<?php

namespace Modules\FutureTrade\Http\Controllers;

use App\Facades\ResponseFacade;
use Illuminate\Routing\Controller;
use Modules\FutureTrade\Http\Requests\Api\FutureGetCandlesRequest;
use Modules\FutureTrade\Services\TradeServices\TradingViewChartService;

class FutureChartController extends Controller
{
    public function getCandles(FutureGetCandlesRequest $request, TradingViewChartService $service): mixed
    {
        $response = $service->getCandles($request);
        return ResponseFacade::success($response)->send();
    }
}
