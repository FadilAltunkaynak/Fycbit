<?php

namespace App\Http\Controllers\Api\User;

use App\Facades\ResponseFacade;
use App\Http\Controllers\Controller;
use App\Enums\FundEnum;
use App\Http\Services\CoinPairService;
use App\Http\Services\CoinService;
use App\Http\Services\Logger;
use App\Services\FundTransferService\DataObject\GetWalletBalanceParams;
use App\Services\FundTransferService\FundTransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CoinController extends Controller
{
    public CoinService $service;
    public $pairService;
    public $logger;
    public function __construct()
    {
        $this->service = new CoinService();
        $this->pairService = new CoinPairService();
        $this->logger = new Logger();
    }

    public function getCoinList(Request $request): JsonResponse
    {
        return ResponseFacade::result(
            $this->service->getCoinsList($request)
        )->send();
    }

    /**
     * all coin pair list
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCoinPairList()
    {
        $pairs = $this->pairService->getAllCoinPairs();
        return response()->json($pairs);
    }

    /**
     * get fund transfer wallet balance
     * @param Request $request
     * @return JsonResponse
     */
    public function getFundTransferWalletBalance(Request $request): JsonResponse
    {
        return $this->handlerApiResponse(function () use ($request) {
            $service = new FundTransferService();
            $fromFundType = FundEnum::tryFrom($request->from_fund_type ?? 0);
            $toFundType = FundEnum::tryFrom($request->to_fund_type ?? 0);

            if (!$fromFundType)
                return failed(__("Invalid from fund type"));
            if (!$toFundType)
                return failed(__("Invalid to fund type"));
            if ($fromFundType == $toFundType)
                return failed(__("From and to fund type can't be same"));

            $fromBalance = $service->getWalletBalance(
                fundType: $fromFundType,
                params: new GetWalletBalanceParams(
                    user_id: authId(),
                    coin_type: $request->coin_type
                )
            );

            $toBalance = $service->getWalletBalance(
                fundType: $toFundType,
                params: new GetWalletBalanceParams(
                    user_id: authId(),
                    coin_type: $request->coin_type
                )
            );

            return success(__("Wallet balance get successfully"), [
                'from_balance' => trim_num($fromBalance, 8),
                'to_balance' => trim_num($toBalance, 8)
            ]);
        });
    }
}
