<?php

namespace Modules\P2P\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\P2P\Http\Service\GiftCardService;
use Modules\P2P\Http\Requests\GiftCardDisputeRequest;
use Modules\P2P\Http\Requests\Api\GetOrderListRequest;
use Modules\P2P\Http\Requests\Api\PayNowGiftCardOrder;
use Modules\P2P\Http\Requests\StoreGiftCardAddRequest;
use Modules\P2P\Http\Requests\UpdateGiftCardAddRequest;
use Modules\P2P\Http\Requests\PGiftCardOrderChatRequest;
use Modules\P2P\Http\Requests\PlaceGiftCardOrderRequest;
use Modules\P2P\Http\Requests\Api\FeedbackUpdateGiftCard;

class GiftCardController extends Controller
{
    private $giftCardService;

    public function __construct()
    {
        $this->giftCardService = new GiftCardService;
    }
    public function storeGiftCardAdds(StoreGiftCardAddRequest $request)
    {
        $response = $this->giftCardService->storeGiftCardAdds($request);
        
        return response()->json($response);
    }
    public function updateGiftCardAdds(UpdateGiftCardAddRequest $request)
    {
        $response = $this->giftCardService->updateGiftCardAdds($request);
        
        return response()->json($response);
    }

    public function giftCardDetails(Request $request)
    {
        $response = $this->giftCardService->giftCardDetails($request);
        
        return response()->json($response);
    }

    public function giftCardDelete(Request $request)
    {
        $response = $this->giftCardService->giftCardDelete($request);
        
        return response()->json($response);
    }

    public function statusChangeGiftCardAds(Request $request)
    {
        $response = $this->giftCardService->statusChangeGiftCardAds($request);
        
        return response()->json($response);
    }

    public function userGiftCardAdsList(Request $request)
    {
        $response = $this->giftCardService->userGiftCardAdsList($request);

        return response()->json($response);
    }

    public function allGiftCardAdsList(Request $request)
    {
        $response = $this->giftCardService->allGiftCardAdsList($request);

        return response()->json($response);
    }

    public function placeGiftCardOrder(PlaceGiftCardOrderRequest $request)
    {
        $response = $this->giftCardService->placeGiftCardOrder($request);

        return response()->json($response);
    }

    public function payNowGiftCardOrder(PayNowGiftCardOrder $request)
    {
        $response = $this->giftCardService->payNowGiftCardOrder($request);

        return response()->json($response);
    }

    public function paymentConfirmGiftCardOrder(Request $request)
    {
        $response = $this->giftCardService->paymentConfirmGiftCardOrder($request);

        return response()->json($response);
    }

    public function cancelGiftCardOrder(Request $request)
    {
        $response = $this->giftCardService->cancelGiftCardOrder($request);

        return response()->json($response);
    }

    public function sendMessage(PGiftCardOrderChatRequest $request)
    {
        $response = $this->giftCardService->sendMessage($request);

        return response()->json($response);
    }

    public function disputeOrderProcess(GiftCardDisputeRequest $request)
    {
        $response = $this->giftCardService->disputeOrderProcess($request);

        return response()->json($response);
    }

    public function getGiftCardPageData()
    {
        $response = $this->giftCardService->getGiftCardPageData();
        return response()->json($response);
    }

    public function getGiftCardData(Request $request)
    {
        return response()->json(
            $this->giftCardService->getGiftCardData($request)
        );
    }

    public function getGiftCardAdsDetails(Request $request)
    {        
        return response()->json(
            $this->giftCardService->getGiftCardAdsDetails($request)
        );
    }

    public function filterGiftCardAds(Request $request)
    {        
        return response()->json(
            $this->giftCardService->filterGiftCardAds($request)
        );
    }

    public function getGiftCardOrder(Request $request)
    {        
        return response()->json(
            $this->giftCardService->getGiftCardOrder($request)
        );
    }

    public function getGiftCardOrdersList(GetOrderListRequest $request)
    {        
        return response()->json(
            $this->giftCardService->getGiftCardOrdersList($request)
        );
    }

    public function getGiftCardTradeHeader()
    {        
        return response()->json(
            $this->giftCardService->getGiftCardTradeHeader()
        );
    }

    public function getGiftCardDetails($gift_card_id)
    {        
        return response()->json(
            $this->giftCardService->getGiftCardDetails($gift_card_id)
        );
    }

    public function updateGiftCardOrderFeedback(FeedbackUpdateGiftCard $request)
    {        
        return response()->json(
            $this->giftCardService->orderFeedback($request)
        );
    }
}
