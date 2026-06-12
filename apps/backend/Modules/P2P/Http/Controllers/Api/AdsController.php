<?php

namespace Modules\P2P\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\P2P\Http\Service\AdsService;
use Modules\P2P\Http\Controllers\BaseController;
use Modules\P2P\Http\Requests\Api\AdsEditRequest;
use Modules\P2P\Http\Requests\Api\GetMarketPrice;
use Modules\P2P\Http\Requests\Api\AdsCreateRequest;
use Modules\P2P\Http\Requests\Api\adsDetailsRequest;
use Modules\P2P\Http\Requests\Api\adsDetailsRequestNew;
use Modules\P2P\Http\Requests\Api\AdsPriceGetRequest;
use Modules\P2P\Http\Requests\Api\AdsFilterUserRequest;
use Modules\P2P\Http\Requests\Api\AdsFilterChangeRequest;
use Modules\P2P\Http\Requests\Api\AdsStatusChangeRequest;
use Modules\P2P\Http\Requests\Api\AvailableBalanceRequest;

class AdsController extends BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new AdsService;
    }

    public function adsCreate(AdsCreateRequest $request)
    {
        try {
            $response = $this->service->createAds($request);
            return $this->sendBackResponse($response,[],true);
        } catch (\Exception $e) {
            storeException('adsCreate Controller', $e->getMessage());
            return response()->json(responseData(false,__("Something went wrong")));
        }
    }

    public function availableBalance(AvailableBalanceRequest $request)
    {
        try {
            $response = $this->service->availableBalance($request);
            return $this->sendBackResponse($response,[],true);
        } catch (\Exception $e) {
            storeException('availableBalance Controller', $e->getMessage());
            return response()->json(responseData(false,__("Something went wrong")));
        }
    }

    public function adsCreateSetting()
    {
        try {
            $response = $this->service->adsCreateSetting();
            return $this->sendBackResponse($response,[],true);
        } catch (\Exception $e) {
            storeException('adsCreateSetting Controller', $e->getMessage());
            return response()->json(responseData(false,__("Something went wrong")));
        }
    }
    public function adsPriceGet(AdsPriceGetRequest $request)
    {
        try {
            $response = $this->service->adsPriceGet($request);
            return $this->sendBackResponse($response,[],true);
        } catch (\Exception $e) {
            storeException('adsPriceGet Controller', $e->getMessage());
            return response()->json(responseData(false,__("Something went wrong")));
        }
    }

    public function adsStatusChange(AdsStatusChangeRequest $request)
    {
        try {
            $response = $this->service->adsStatusChange($request);
            return $this->sendBackResponse($response,[],true);
        } catch (\Exception $e) {
            storeException('adsStatusChange Controller', $e->getMessage());
            return response()->json(responseData(false,__("Something went wrong")));
        }
    }
    public function adsFilterChange(AdsFilterChangeRequest $request)
    {
        try {
            $response = $this->service->adsFilterChange($request);
            return $this->sendBackResponse($response,[],true);
        } catch (\Exception $e) {
            storeException('adsFilterChange Controller', $e->getMessage());
            return response()->json(responseData(false,__("Something went wrong")));
        }
    }

    public function userAdsFilterChange(AdsFilterUserRequest $request)
    {
        try {
            $response = $this->service->userAdsFilterChange($request);
            return $this->sendBackResponse($response,[],true);
        } catch (\Exception $e) {
            storeException('userAdsFilterChange Controller', $e->getMessage());
            return response()->json(responseData(false,__("Something went wrong")));
        }
    }

    public function getMarketPrice(GetMarketPrice $request)
    {
        try {
            $response = $this->service->getMarketPrice($request);
            return $this->sendBackResponse($response,[],true);
        } catch (\Exception $e) {
            storeException('getMarketPrice Controller', $e->getMessage());
            return response()->json(responseData(false,__("Something went wrong")));
        }
    }

    public function adsDetails(adsDetailsRequestNew $request)
    {
        try {
            $response = $this->service->adsDetails($request);
            return $this->sendBackResponse($response,[],true);
        } catch (\Exception $e) {
            storeException('adsDetails Controller', $e->getMessage());
            return response()->json(responseData(false,__("Something went wrong")));
        }
    }

    public function adsEdit(AdsEditRequest $request)
    {
        try {
            $response = $this->service->adsEdit($request);
            return $this->sendBackResponse($response,[],true);
        } catch (\Exception $e) {
            storeException('adsDetails Controller', $e->getMessage());
            return response()->json(responseData(false,__("Something went wrong")));
        }
    }

    public function adsDelete(adsDetailsRequest $request)
    {
        try {
            $response = $this->service->adsDelete($request);
            return $this->sendBackResponse($response,[],true);
        } catch (\Exception $e) {
            storeException('adsDetails Controller', $e->getMessage());
            return response()->json(responseData(false,__("Something went wrong")));
        }
    }

    public function adsMarketSetting()
    {
        try {
            $response = $this->service->adsMarketSetting();
            return $this->sendBackResponse($response,[],true);
        } catch (\Exception $e) {
            storeException('adsMarketSetting Controller', $e->getMessage());
            return response()->json(responseData(false,__("Something went wrong")));
        }
    }

    public function myAdsDetails(adsDetailsRequest $request)
    {
        try {
            $response = $this->service->myAdsDetails($request);
            return $this->sendBackResponse($response,[],true);
        } catch (\Exception $e) {
            storeException('myAdsDetails Controller', $e->getMessage());
            return response()->json(responseData(false,__("Something went wrong")));
        }
    }
}
