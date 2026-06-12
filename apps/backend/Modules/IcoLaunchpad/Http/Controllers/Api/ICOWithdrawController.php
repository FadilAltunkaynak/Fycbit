<?php

namespace Modules\IcoLaunchpad\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\IcoLaunchpad\Http\Requests\ICOWithdrawRequest;
use Modules\IcoLaunchpad\Http\Services\ICOWithdrawService;

class ICOWithdrawController extends Controller
{
    private $service;
    public function __construct()
    {
        $this->service = new ICOWithdrawService();
    }
    public function getTokenEarnig(Request $request)
    {
        try {
            $respons = $this->service->getTokenEarnigs();
            return response()->json($respons);
        } catch (\Exception $e) {
            storeException('getTokenEarnig ex', $e->getMessage());
            return response()->json(responseData(false, __("Something went wrong")));
        }
    }

    public function getTokenWithdrawlPrice(ICOWithdrawRequest $request)
    {
        try {
            $respons = $this->service->getTokenWithdrawlPrice($request);
            return response()->json($respons);
        } catch (\Exception $e) {
            storeException('getTokenWithdrawlPrice ex', $e->getMessage());
            return response()->json(responseData(false, __("Something went wrong")));
        }
    }

    public function getTokenWithdrawlRequest(ICOWithdrawRequest $request)
    {
        try {
            $respons = $this->service->getTokenWithdrawlRequest($request);
            return response()->json($respons);
        } catch (\Exception $e) {
            storeException('getTokenWithdrawlPrice ex', $e->getMessage());
            return response()->json(responseData(false, __("Something went wrong")));
        }
    }

    public function getTokenWithdrawalList(Request $request)
    {
        try {
            $respons = $this->service->getTokenWithdrawalList($request);
            return response()->json($respons);
        } catch (\Exception $e) {
            storeException('getTokenWithdrawlList ex', $e->getMessage());
            return response()->json(responseData(false, __("Something went wrong")));
        }
    }
}
