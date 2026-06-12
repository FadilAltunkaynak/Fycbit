<?php

namespace Modules\P2P\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\P2P\Http\Service\P2pTradeService;
use Modules\P2P\Http\Controllers\BaseController;
use Modules\P2P\Http\Requests\Api\MyOrderRequest;
use Modules\P2P\Http\Requests\Api\P2pRateRequest;
use Modules\P2P\Http\Requests\OrderCancelRequest;
use Modules\P2P\Http\Requests\Api\FeedBackRequest;
use Modules\P2P\Http\Requests\Api\P2pOrderRequest;
use Modules\P2P\Http\Requests\TradeDisputeRequest;
use Modules\P2P\Http\Requests\Api\OrderDetailsRequest;
use Modules\P2P\Http\Requests\Api\PaymentOrderRequest;

class P2pTradeController extends BaseController
{
    private $service;
    function __construct()
    {
        $this->service = new P2pTradeService();
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function placeOrder(P2pOrderRequest $request)
    {
        $response = responseData(false,__('Something went wrong'));
        try {
            $response = $this->service->p2pOrderPlaceProcess($request);
        } catch(\Exception $e) {
            storeException('p2p placeOrder',$e->getMessage());
        }
        return response()->json($response);
    }
    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function paymentOrder(PaymentOrderRequest $request)
    {
        $response = responseData(false,__('Something went wrong'));
        try {
            $response = $this->service->p2pOrderPaymentProcess($request);
        } catch(\Exception $e) {
            storeException('p2p paymentOrder ex',$e->getMessage());
        }
        return response()->json($response);
    }
    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function releaseP2pOrder(Request $request)
    {
        $response = responseData(false,__('Something went wrong'));
        try {
            $response = $this->service->p2pOrderReleaseProcess($request);
        } catch(\Exception $e) {
            storeException('p2p releaseP2pOrder ex',$e->getMessage());
        }
        return response()->json($response);
    }

    /**
     * Show the specified resource.
     * @param Request $request
     * @return Response
     */
    public function getP2pOrderRate(P2pRateRequest $request)
    {
        $response = responseData(false,__('Something went wrong'));
        try {
            $response = $this->service->getP2pOrderRate($request);
        } catch(\Exception $e) {
            storeException('p2p placeOrder',$e->getMessage());
        }
        return response()->json($response);
    }

    /**
     * Show the specified resource.
     * @param Request $request
     * @return Response
     */
    public function orderDetails(OrderDetailsRequest $request)
    {
        $response = responseData(false,__('Something went wrong'));
        try {
            $response = $this->service->getP2pOrderDetails($request);
        } catch(\Exception $e) {
            storeException('p2p placeOrder',$e->getMessage());
        }
        return response()->json($response);
    }

    public function disputeProcess(TradeDisputeRequest $request)
    {
        $checkDisputValidation = $this->service->checkDisputeValidation($request->order_uid);

        if($checkDisputValidation['success'])
        {
            $order_details = $checkDisputValidation['data'];
            $response = $this->service->createDispute($request, $order_details);

            return response()->json($response);
        }else{
            return response()->json($checkDisputValidation);
        }
    }

    public function cancelP2pOrder(OrderCancelRequest $request)
    {
        try {
            $response = $this->service->cancelP2pOrder($request);
            return $this->sendBackResponse($response,[],true);
        } catch (\Exception $e) {
            storeException("cancelP2pOrder p2p", $e->getMessage());
        }
    }

    public function myP2pOrder(MyOrderRequest $request)
    {
        try {
            $response = $this->service->myP2pOrder($request);
            return $this->sendBackResponse($response,[],true);
        } catch (\Exception $e) {
            storeException("cancelP2pOrder p2p", $e->getMessage());
        }
    }


    public function myP2pDisputeOrder(MyOrderRequest $request)
    {
        try {
            $user = authUser_p2p();
            $response = $this->service->myDisputeList($request,$user);
            return $this->sendBackResponse($response,[],true);
        } catch (\Exception $e) {
            storeException("cancelP2pOrder p2p", $e->getMessage());
        }
    }

    public function orderFeedback(FeedBackRequest $request)
    {
        try {
            $response = $this->service->orderFeedback($request);
            return $this->sendBackResponse($response,[],true);
        } catch (\Exception $e) {
            storeException("orderFeedback p2p", $e->getMessage());
        }
    }

    public function myOrderListData()
    {
        try {
            $response = $this->service->myOrderListData();
            return $this->sendBackResponse($response,[],true);
        } catch (\Exception $e) {
            storeException("myOrderListData p2p", $e->getMessage());
        }
    }

}
