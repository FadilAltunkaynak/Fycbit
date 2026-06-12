<?php

namespace Modules\P2P\Http\Controllers;

use App\Model\GiftCard;
use App\Model\AdminSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\P2P\Entities\PGiftCard;
use Modules\P2P\Entities\PGiftCardOrder;
use Modules\P2P\Http\Service\GiftCardService;
use Modules\P2P\Http\Requests\PGiftCardOrderChatRequest;

class GiftCardController extends Controller
{
    private $giftCardService;

    public function __construct()
    {
        $this->giftCardService = new GiftCardService;
    }
    public function disputeGiftCardList(Request $request)
    {
        $data = [];
        try {
            if($request->ajax())
            {
                $disputed_list = PGiftCardOrder::with(['buyer', 'seller','dispute_details'])->where('is_reported','<>',0)->get();
                return datatables()->of($disputed_list)
                    ->addColumn('buyer_name', function ($query) {
                        return isset($query->buyer)?$query->buyer->first_name .' '. $query->buyer->last_name : __('N/A');
                    })
                    ->addColumn('seller_name', function ($query) {
                        return isset($query->seller)?$query->seller->first_name .' '. $query->seller->last_name : __('N/A');
                    })
                    ->addColumn('status', function ($query) {
                        return statusOnDisputeOrder_p2p($query);
                    })
                    ->addColumn('created_at', function ($query) {
                        return $query->created_at;
                    })
                    ->addColumn('action', function ($query) {
                        return ActionButtonForDispute_p2p($query, 'disputeGiftCardDetails');
                    })
                    ->rawColumns(['status','action'])
                    ->make(true);
            }
        } catch (\Exception $e) {
            storeException('disputeGiftCardList', $e->getMessage());
        }
        $data['title'] = __("Disputed List");
        return view('p2p::dispute.gift-card-dispute-list',$data);
    }

    public function disputeGiftCardDetails($uid)
    {
        $data['title'] = __("Disputed Details");
        
        $response = $this->giftCardService->getOrderDisputedDetails($uid);
        if($response['success'])
        {
            $data['order_details'] = $response['data'];
            $order_id = $response['data']['id'];
            $dispute_id = isset($response['data']['dispute_details'])?$response['data']['dispute_details']['id']:0;
            $conversation_response = $this->giftCardService->getConversationListForDisputeOrder($order_id,$dispute_id);
            if($conversation_response['success'])
            {
                $data['conversation_list'] = $conversation_response['data'];
            }
            return view('p2p::dispute.gift-card-order-details',$data);
        }else{
            return back()->with('dismiss', $response['message']);
        }
    }

    public function giftCardSendMessage(PGiftCardOrderChatRequest $request)
    {
        $response = $this->giftCardService->sendMessage($request);
        return response()->json($response);
    }

    public function refundDisputeDetails(Request $request)
    {
        $response = $this->giftCardService->refundDisputeDetailsByAdmin($request);
        
        if($response['success'])
        {
            return back()->with('success', $response['message']);
        }else{
            return back()->with('dismiss', $response['message']);
        }
    }

    public function releaseDisputeDetails(Request $request)
    {
        $response = $this->giftCardService->releaseDisputeDetailsByAdmin($request);
        
        if($response['success'])
        {
            return back()->with('success', $response['message']);
        }else{
            return back()->with('dismiss', $response['message']);
        }
    }

    public function giftCardaAssignDisputeDetails(Request $request)
    {
        $response = $this->giftCardService->assignDisputeDetailsToAdmin($request);

        if($response['success'])
        {
            return back()->with('success', $response['message']);
        }else{
            return back()->with('dismiss', $response['message']);
        }
    }

    public function getGiftCardAdsHistory(Request $request)
    {
        if($request->ajax()){
            $ads = PGiftCard::with(['user', 'gift_card']);

            if(isset($request->status) && $request->status !== 'all')
                $ads = $ads->where('status', $request->status)->orderBy('created_at', 'DESC')->get();
            else
                $ads = $ads->orderBy('created_at', 'DESC')->get();

            return datatables()->of($ads)
                ->addColumn('user', function($ads){
                    return $ads->user->email;
                })
                ->editColumn('price', function($ads){
                    return $ads->price. ' ' .$ads->currency_type;
                })
                ->editColumn('amount', function($ads){
                    return $ads->gift_card->amount. ' ' .$ads->gift_card->coin_type;
                })
                ->editColumn('status', function($ads){
                    return getGiftCardAdStatus($ads->status);
                })
                ->rawColumns(['price','amount','status'])
                ->make(true);
        }
        return view('p2p::gift_card.ads_history.history');
    }

    public function getGiftCardOrderHistory(Request $request)
    {
        if($request->ajax()){
            $order = PGiftCardOrder::with(['buyer', 'seller','p_gift_card.gift_card']);

            if(isset($request->status) && $request->status !== 'all')
                $order = $order->where('status', $request->status)->orderBy('created_at', 'DESC')->get();
            else
                $order = $order->orderBy('created_at', 'DESC')->get();
           
            return datatables()->of($order)
                ->addColumn('buyer', function($order){
                    return $order->buyer->email;
                })
                ->addColumn('seller', function($order){
                    return $order->seller->email;
                })
                ->editColumn('price', function($order){
                    return $order->price. ' ' .$order->currency_type;
                })
                ->editColumn('amount', function($order){
                    return $order?->p_gift_card?->gift_card?->amount. ' ' .$order?->p_gift_card?->gift_card?->coin_type;
                })
                ->editColumn('status', function($order){
                    return gitCardTradeStatusListP2P($order->status);
                })
                ->addColumn('card_details', function($order){
                    return '<button class="btn btn-primary" onclick="getGiftCardDetails('. $order->p_gift_card_id .')">'. __("Card Details") . '</button>';
                })
                ->rawColumns(['price','amount','status','card_details'])
                ->make(true);
        }
        return view('p2p::gift_card.order_history.history');
    }

    public function getGiftHeader()
    {
        $data['setting'] = settings(['gift_card_trade_page_header','gift_card_trade_page_description','gift_card_trade_page_image']);
        return view('p2p::gift_card.header.header', $data);
    }
    public function saveGetGiftHeader(Request $request)
    {
        try{
            // My Gift Cards Trade Themes Header Settings
            if(isset($request->gift_card_trade_page_header))
            AdminSetting::updateOrCreate(['slug'=>'gift_card_trade_page_header'],['value'=> $request->gift_card_trade_page_header ?? ""]);
            if(isset($request->gift_card_trade_page_description))
            AdminSetting::updateOrCreate(['slug'=>'gift_card_trade_page_description'],['value'=> $request->gift_card_trade_page_description ?? ""]);
            if($request->hasFile('gift_card_trade_page_image')){
                $setting = settings(['gift_card_trade_page_image']);
                $old_image       = isset($setting) ? $setting['gift_card_trade_page_image'] ?? null : null;
                if(isset($setting)) deleteFile(public_path(IMG_PATH), $old_image);
                $image           = uploadFile($request->file('gift_card_trade_page_image'),IMG_PATH);
                AdminSetting::updateOrCreate(['slug'=>'gift_card_trade_page_image'],['value'=> $image ?? ""]);
            }
            return redirect()->back()->with('success', __("Header details saved successfully"));
        } catch(\Exception $e) {
            storeException('saveGetGiftHeader', $e->getMessage());
            return redirect()->back()->with('success', __("Something went wrong"));
        }
    }

    public function getGiftCardDetailsAtOrderHistory(Request $request){
        try{
            $ads_id = $request->id ?? 0;
            if($ads = PGiftCard::find($ads_id)){
                if($card = GiftCard::with(['banner','banner.category'])->find($ads->gift_card_id)){
                    return responseData(true, __("Gift card details found successfully"), $card);
                } return responseData(false, __("Gift card not found!"));
            } return responseData(false, __("Ads not found!"));
        } catch(\Exception $e) {
            storeException('getGiftCardDetailsAtOrderHistory', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
}
