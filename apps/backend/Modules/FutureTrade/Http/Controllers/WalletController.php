<?php

namespace Modules\FutureTrade\Http\Controllers;

use App\Facades\ResponseFacade;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FutureTrade\Services\OrderService\OrderSummaryService;
use Modules\FutureTrade\Services\WalletServices\WalletService;

class WalletController extends Controller
{
    public function getWalletDetails(WalletService $service): mixed
    {
        $response = $service->userWalletDetails();
        return ResponseFacade::result($response)->send();
    }

    public function walletDetailsWithBalance(WalletService $service): mixed
    {
        $response = $service->getWalletDetailsWithBalance();
        return ResponseFacade::result($response)->send();
    }

    public function getUserAllFutureWallet(WalletService $service): mixed
    {
        $response = $service->getUserAllFutureWallet();
        return ResponseFacade::result($response)->send();
    }

    public function getTodayPnl(WalletService $service): mixed
    {
        $response = $service->getTodayPnl();
        return ResponseFacade::result($response)->send();
    }

    public function getCombinedWalletData(WalletService $service): mixed
    {
        $response = $service->getCombinedWalletData();
        return ResponseFacade::result($response)->send();
    }

    public function getOpenOrderAndPositionCost(WalletService $service): mixed
    {
        $response = $service->getOpenOrderAndPositionCost();
        return ResponseFacade::success($response)->send();
    }

    public function getOpenOrderAndPositionAmount(Request $request, OrderSummaryService $service): mixed
    {

        if(!$request->filled('coin_pair_uid')){
            return ResponseFacade::failed(__("Coin pair uid is required"))->send();
        }

        $response = $service->getOpenOrdersAndPositionsSummary($request->coin_pair_uid);
        return ResponseFacade::success($response)->send();
    }
}
