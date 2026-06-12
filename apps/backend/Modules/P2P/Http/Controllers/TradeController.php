<?php

namespace Modules\P2P\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\P2P\Entities\POrder;
use Modules\P2P\Http\Service\P2pTradeService;
use Modules\P2P\Http\Service\ConversationService;

class TradeController extends Controller
{
    private $tradeService;
    private $conversationService;
    public function __construct()
    {
        $this->tradeService = new P2pTradeService;
        $this->conversationService  = new ConversationService;
    }
    public function getDisputedList(Request $request)
    {
        $data = [];
        try {
            if($request->ajax())
            {
                $disputed_list = POrder::with(['buyer', 'seller','dispute_details'])->where('is_reported','<>',0)->get();
                return datatables()->of($disputed_list)
                    ->addColumn('buyer_name', function ($query) {
                        return isset($query->buyer)?$query->buyer->first_name .' '. $query->buyer->last_name : __('N/A');
                    })
                    ->addColumn('seller_name', function ($query) {
                        return isset($query->seller)?$query->seller->first_name .' '. $query->seller->last_name : __('N/A');
                    })
                    ->addColumn('payment_type', function ($query) {
                        return find_payment_type_p2p($query->payment_type);
                    })
                    ->addColumn('status', function ($query) {
                        return statusOnDisputeOrder_p2p($query);
                    })
                    ->addColumn('created_at', function ($query) {
                        return $query->created_at;
                    })
                    ->addColumn('action', function ($query) {
                        return ActionButtonForDispute_p2p($query, 'getDisputeDetails');
                    })
                    ->rawColumns(['status','action'])
                    ->make(true);
            }
        } catch (\Exception $e) {
            storeException('getDisputedList', $e->getMessage());
        }
        $data['title'] = __("Disputed List");
        return view('p2p::dispute.list',$data);
    }

    public function getDisputeDetails($uid)
    {
        $data['title'] = __("Disputed Details");
        
        $response = $this->tradeService->getDisputedDetails($uid);
        if($response['success'])
        {
            $data['order_details'] = $response['data'];
            $order_id = $response['data']['id'];
            $dispute_id = isset($response['data']['dispute_details'])?$response['data']['dispute_details']['id']:0;
            $conversation_response = $this->conversationService->getConversationListForDisputeOrder($order_id,$dispute_id);
            if($conversation_response['success'])
            {
                $data['conversation_list'] = $conversation_response['data'];
            }
            return view('p2p::dispute.details',$data);
        }else{
            return back()->with('dismiss', $response['message']);
        }
        
    }

    public function assignDisputeDetails(Request $request)
    { 
        $response = $this->tradeService->assignDisputeDetailsToAdmin($request);

        if($response['success'])
        {
            return back()->with('success', $response['message']);
        }else{
            return back()->with('dismiss', $response['message']);
        }
    }

    public function releaseDisputeDetails(Request $request)
    {
        $response = $this->tradeService->releaseDisputeDetailsByAdmin($request);
        
        if($response['success'])
        {
            return back()->with('success', $response['message']);
        }else{
            return back()->with('dismiss', $response['message']);
        }
    }

    public function refundDisputeDetails(Request $request)
    {
        $response = $this->tradeService->refundDisputeDetailsByAdmin($request);
        
        if($response['success'])
        {
            return back()->with('success', $response['message']);
        }else{
            return back()->with('dismiss', $response['message']);
        }
    }

   public function getOrderList($status, Request $request)
   {
        try {
            
            if($request->ajax())
            {
                $order_list = POrder::with(['buyer', 'seller'])->where('status', $status)->latest()->get();
                return datatables()->of($order_list)
                    ->addColumn('buyer_name', function ($query) {
                        return isset($query->buyer)?$query->buyer->first_name .' '. $query->buyer->last_name : __('N/A');
                    })
                    ->addColumn('seller_name', function ($query) {
                        return isset($query->seller)?$query->seller->first_name .' '. $query->seller->last_name : __('N/A');
                    })
                    ->addColumn('payment_type', function ($query) {
                        return find_payment_type_p2p($query->payment_type);
                    })
                    ->addColumn('order_type', function ($query) {
                        if(isset($query->buy_id))
                        {
                            return __('Buy');
                        }else{
                            return __('Sell');
                        }
                    })
                    ->addColumn('status', function ($query) {
                        return tradeStatusListP2P($query->status);
                    })
                    ->addColumn('reported', function ($query) {
                        if($query->is_reported == 0)
                        {
                            return __('No');
                        }else{
                            return __('Yes');
                        }
                    })
                    ->addColumn('created_at', function ($query) {
                        return $query->created_at;
                    })
                    ->addColumn('action', function ($query) {
                        return ActionButtonForOrderP2P($query, 'getOrderDetails');
                    })
                    ->rawColumns(['status','action'])
                    ->make(true);
            }
        } catch (\Exception $e) {
            storeException('getDisputedList', $e->getMessage());
        }
        $data['status'] = $status;
        $data['title'] = __('Order List');
        return view('p2p::trade.order-list',$data);
   }

   public function getOrderDetails($uid)
   {
        $data['title'] = __('Order Details');
        $response = $this->tradeService->getOrderDetails($uid);

        if($response['success'])
        {
            $data['order_details'] = $response['data'];
            return view('p2p::trade.order-details', $data);
        }else{
            return back()->with('dismiss', $response['message']);
        }
    }

    public function getUserTradeDetails($user_id)
    {
        $data['title'] = __('User Trade Details');
        $response = $this->tradeService->getUserTradeDetails(decrypt($user_id));

        if($response['success'])
        {
            $data['order_details'] = $response['data'];
            return view('p2p::trade.user-trade-details', $response['data']);
        }else{
            return back()->with('dismiss', $response['message']);
        }

    }
}
