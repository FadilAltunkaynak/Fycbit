<?php

namespace Modules\IcoLaunchpad\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\IcoLaunchpad\Http\Requests\IcoBuyPriceRequest;
use Modules\IcoLaunchpad\Http\Requests\IcoTokenBuyRequest;
use Modules\IcoLaunchpad\Http\Services\IcoTokenBuyService;

class IcoTokenBuyController extends Controller
{
    private $service;
    function __construct()
    {
        $this->service = new IcoTokenBuyService();
    }
    // ico token buy post request
    public function makeRequest(IcoTokenBuyRequest $request)
    {
        try {
            $id = auth()->id() ?? auth()->guard('api')->id();
            $request->merge(['user_id' => $id]);
            $response = $this->service->tokenBuyRequest($request);
            return response()->json($response);
        } catch (\Exception $e) {
            storeException("tokenBuyRequest:", $e->getMessage());
            return response()->json(['success' => false, 'message' => __('Something went wrong')]);
        }
    }

    public function makeRequestNew(IcoTokenBuyRequest $request)
    {
        try {
            $id = auth()->id() ?? auth()->guard('api')->id();
            $request->merge(['user_id' => $id]);
            $response = $this->service->tokenBuyRequestNew($request);
            return response()->json($response);
        } catch (\Exception $e) {
            storeException("tokenBuyRequest:", $e->getMessage());
            return response()->json(['success' => false, 'message' => __('Something went wrong')]);
        }
    }

    public function getPageData()
    {
        try {
            $response = $this->service->getTokenBuyPageData();
            return response()->json($response);
        } catch (\Exception $e) {
            storeException("tokenBuyGetPageData:", $e->getMessage());
            return response()->json(['success' => false, 'message' => __('Something went wrong')]);
        }
    }

    public function getTokenBuyHistory(Request $request, $type = false)
    {
        try {
            $response = $this->service->getTokenBuyHistory($request, $type);
            return response()->json($response);
        } catch (\Exception $e) {
            storeException("tokenBuyGetPageData:", $e->getMessage());
            return response()->json(['success' => false, 'message' => __('Something went wrong')]);
        }
    }

    public function checkPhase(Request $request)
    {
        try {
            $response = $this->service->checkPhase($request);
            return response()->json($response);
        } catch (\Exception $e) {
            storeException("checkPhaseController:", $e->getMessage());
            return response()->json(['success' => false, 'message' => __('Something went wrong')]);
        }
    }

    public function getPriceInfo(IcoBuyPriceRequest $request)
    {
        try {
            $id = auth()->id() ?? auth()->guard('api')->id();
            $request->merge(['user_id' => $id]);
            $response = $this->service->getPriceInfo($request);
            return response()->json($response);
        } catch (\Exception $e) {
            storeException("getPriceInfo", $e->getMessage());
            return response()->json(['success' => false, 'message' => __('Something went wrong')]);
        }
    }

    // get ico token wallet
    public function getUserTokenBalanceList(Request $request)
    {
        try {
            $response = $this->service->getUserTokenWalletList($request);
            return response()->json($response);
        } catch (\Exception $e) {
            storeException("getUserTokenBalanceList", $e->getMessage());
            return response()->json(['success' => false, 'message' => __('Something went wrong')]);
        }
    }
}
