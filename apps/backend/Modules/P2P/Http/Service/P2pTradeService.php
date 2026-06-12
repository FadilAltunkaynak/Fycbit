<?php
namespace Modules\P2P\Http\Service;

use App\User;
use Carbon\Carbon;
use App\Model\AdminSetting;
use Modules\P2P\Entities\PBuy;
use Modules\P2P\Entities\PSell;
use Modules\P2P\Entities\POrder;
use Illuminate\Support\Facades\DB;
use Modules\P2P\Entities\P2PWallet;
use Illuminate\Support\Facades\Auth;
use Modules\P2P\Entities\POrderChat;
use Modules\P2P\Jobs\CancelOrderJob;
use App\Model\DynamicBank\BankRecord;
use Modules\P2P\Jobs\ReleaseOrderJob;
use Modules\P2P\Entities\PCoinSetting;
use Modules\P2P\Entities\POrderDispute;
use Modules\P2P\Jobs\RefundDisputeOrderJob;
use Modules\P2P\Jobs\SendEmailOnChangeState;

class P2pTradeService
{
    private $repo;

    public function __construct()
    {
    }

    // get p2p order place rate
    public function getP2pOrderRate($request)
    {
        return $this->getP2pAdsRate($request);
    }
    // order place process
    public function p2pOrderPlaceProcess($request)
    {
        $response =  responseData(false,__('Something went wrong 2'));
        try {
            $user = Auth::user();
            $checkValidation = $this->p2pTradePlaceOrderValidation($request,$user);

            if($checkValidation['success'] == true) {
                $data = $checkValidation['data'];
                $inputData = $data['data'];
                $wallets = $data['wallets'];
                DB::beginTransaction();
                try {
                    $ads = $data['ads'];
                    $order = POrder::create($inputData);
                    POrderChat::create($this->makeChatData($user->id,$order,$request->message_text ?? 'Hello'));
                    if (!empty($ads->auto_reply)) {
                        POrderChat::create($this->makeChatData($ads->user_id,$order,$ads->auto_reply));
                    }
                    if($request->ads_type == TRADE_BUY_TYPE) {
                        $p2pAmount = bcadd($inputData['amount'],$inputData['seller_fees'],8);
                    } else {
                        $p2pAmount = $inputData['amount'];
                        $sellerAmount = bcadd($inputData['amount'],$inputData['seller_fees'],8);
                        $wallets['seller_wallet']->decrement('balance',$sellerAmount);
                    }
                    $ads->increment('sold',$p2pAmount);
                    $ads->decrement('available',$p2pAmount);

                } catch(\Exception $e) {
                    storeException('p2pOrderPlaceProcess ex1',$e->getMessage());
                    DB::rollBack();
                    return responseData(false,__('P2P tarde opened failed'));
                }
                DB::commit();
                $response =  responseData(true,__('P2P tarde opened successfully'),$order);
            } else {
                $response = $checkValidation;
            }

        } catch(\Exception $e) {
            storeException('p2pOrderPlaceProcess ex',$e->getMessage());
        }
        return $response;
    }

    // make chat data
    public function makeChatData($userId,$order,$msg,$file=null)
    {
        return [
            'sender_id' => $userId,
            'receiver_id' => $order->buyer_id == $userId ? $order->seller_id : $order->buyer_id,
            'message' => $msg,
            'order_id' => $order->id
        ];
    }

    // order place validation
    public function p2pTradePlaceOrderValidation($request,$user)
    {
        $response =  responseData(false,__('Something went wrong'));
        try {
            $data = [];
            $wallets = [];
            if($request->ads_type == TRADE_BUY_TYPE) {
                $ads = PSell::where(['uid' => $request->ads_id])->with('coin')->first();
                $data['sell_id'] = $ads->id;
                $data['buyer_id'] = $user->id;
                $data['seller_id'] = $ads->user_id;
            } else {
                $ads = PBuy::where(['uid' => $request->ads_id])->with('coin')->first();
                $data['buy_id'] = $ads->id;
                $data['seller_id'] = $user->id;
                $data['buyer_id'] = $ads->user_id;
            }
            if ($ads) {
                $coinSetting =  $ads->coin()->first();
                $buyFees = $coinSetting->buy_fees ?? 0;
                $sellFees = $coinSetting->sell_fees ?? 0;
                $checkOrder = $this->checkOderValidation($ads,$user,$request);
                if ($checkOrder['success'] == false) {
                    return $checkOrder;
                }
                $data['payment_id'] = $checkOrder['data']['payment_id'];
                $data['payment_time'] = $checkOrder['data']['payment_time'];
                $data['payment_expired_time'] = $checkOrder['data']['payment_expired_time'];
                $p2pRate = $this->getP2pAdsRate($request);

                if ($p2pRate['success'] == false) {
                    return $p2pRate;
                } else {
                    $data['amount'] = $p2pRate['data']['amount'];
                    $data['rate'] = $p2pRate['data']['rate'];
                    $data['price'] = $p2pRate['data']['amount_price'];
                }

                $data['seller_fees'] = getP2pOrderFees($data['amount'],$sellFees);
                $data['seller_fees_percentage'] = $sellFees;
                $data['buyer_fees'] = getP2pOrderFees($data['amount'],$buyFees);
                $data['buyer_fees_percentage'] = $buyFees;
                if($request->ads_type == TRADE_BUY_TYPE) {
                    $data['sell_id'] = $ads->id;
                    $data['buyer_id'] = $user->id;
                    $data['seller_id'] = $ads->user_id;
                    if ($ads->available < ($data['amount'] + $data['seller_fees'])) {
                        return responseData(false,__('Do not have enough ').$ads->coin_type.__(' to buy, the available amount is ').$ads->available.' '.$ads->coin_type .__(' including fess'));
                    }
                    $wallets['buyer_wallet'] = getUserP2pWallet($ads->coin_type,$user->id);
                    $wallets['seller_wallet'] = getUserP2pWallet($ads->coin_type,$ads->user_id);
                    $data['buyer_wallet_id'] = $wallets['buyer_wallet']->id;
                    $data['seller_wallet_id'] = $wallets['seller_wallet']->id;
                } else {
                    $data['buy_id'] = $ads->id;
                    $data['seller_id'] = $user->id;
                    $data['buyer_id'] = $ads->user_id;
                    if ($ads->available < $data['amount']) {
                        return responseData(false,__('Do not have enough ').$ads->coin_type.__(' to place order the available amount is ').$ads->available.' '.$ads->coin_type);
                    }
                    $wallets['buyer_wallet'] = getUserP2pWallet($ads->coin_type,$ads->user_id);
                    $wallets['seller_wallet'] = getUserP2pWallet($ads->coin_type,$user->id);

                    if ($wallets['seller_wallet']->balance < ($data['amount'] + $data['seller_fees'])) {
                        return responseData(false,__('You do not have enough ').$ads->coin_type.__(' to place sell order, please deposit first ').$data['amount'] + $data['seller_fees'].' '.$ads->coin_type .__(' including fess'));
                    }
                    $data['buyer_wallet_id'] = $wallets['buyer_wallet']->id;
                    $data['seller_wallet_id'] = $wallets['seller_wallet']->id;
                }
                $data['coin_type'] = $ads->coin_type;
                $data['currency'] = $ads->currency;
                $data['who_opened'] = $user->id;
                $data['transaction_id'] = makeOrderTransactionId($ads,$data);
                $data['uid'] = pMakeUniqueId();
                $data['order_id'] = '#000000000' . (POrder::count() + 1);

                $response =  responseData(true,__('Validation success'),['ads'=> $ads,'data' => $data, 'wallets' => $wallets]);
                return $response;
            } else {
                $response = responseData(false,__('Buy or sell ad not found'));
            }

        } catch(\Exception $e) {
            storeException('p2pTradePlaceOrderValidation ex',$e->getMessage());
        }
        return $response;
    }

    // check order validation
    public function checkOderValidation($ads,$user,$request)
    {
        $data=[];
        $settings = AdminSetting::get()->toSlugValueP2P();
        $data['payment_time'] = 0;
        $data['payment_expired_time'] = null;
        if ($ads->user_id == $user->id) {
            return responseData(false,__('You can not trade with own account'));
        }
        if (!empty($request->price)) {
            if ($request->price < $ads->minimum_trade_size) {
                return responseData(false,__('The trade amount is below the lister minimum'));
            }
            if ($request->price > $ads->maximum_trade_size) {
                return responseData(false,__('The trade amount is above the lister maximum'));
            }
        }
        $paymentMethod = BankRecord::getUserPaymentMethod($ads->user_id)->where("id", $request->payment_id)->first();
        if ($paymentMethod) {
            $data['payment_id'] = $paymentMethod->id;
            if ($request->ads_type == TRADE_SELL_TYPE) {
                $checkSellerPaymentMethod = BankRecord::getUserPaymentMethod($user->id)->where("form_id", $paymentMethod->form_id)->first();
                if ($checkSellerPaymentMethod) {
                    $data['payment_id'] = $checkSellerPaymentMethod->id;
                } else {
                    return responseData(false,__('Before trade you must add this payment method'));
                }
            }
        } else {
            return responseData(false,__('Payment method not found'));
        }
        if (!empty($ads->payment_times)) {
            $data['payment_time'] = $ads->payment_times;
            $payment_expire_time = Carbon::now();
            $payment_expire_time = $payment_expire_time->addMinutes($ads->payment_times);
            $data['payment_expired_time'] = $payment_expire_time->format('Y-m-d H:i:s');
        }

        //Kyc and Counterparty condition
        if (isset($settings->counterparty_condition) && $settings->counterparty_condition == STATUS_ACTIVE) {
            $time = Carbon::now();
            $userCreateDate = auth()->user()?->created_at?->addDays($ads->register_days ?? 0);
            $result = $time->gte($userCreateDate);
            if(!$result) return responseData(false, __("Your account not over required day, try again later."));

            if($wallet = P2PWallet::where(['user_id' => authUserId_p2p(), 'coin_type' => 'BTC'])->first())
            {
                if($wallet?->balance >= $ads->coin_holding){}
                else return responseData(false, __("You not holding enough BTC"));
            }else{
                return responseData(false, __("Wallet not found"));
            }
        }

        $response =  responseData(true,__('Success'),$data);
        return $response;
    }

    // get p2p ads rate
    public function getP2pAdsRate($request)
    {
        $response =  responseData(false,__('Something went wrong'));
        try {
            if($request->ads_type == TRADE_BUY_TYPE) {
                $ads = PSell::where(['uid' => $request->ads_id])->first();
            } else {
                $ads = PBuy::where(['uid' => $request->ads_id])->first();
            }
            if ($ads) {
                $data['rate'] = $ads->price;
                if (!empty($request->amount)) {
                    $data['amount_price'] = bcmul($ads->price,$request->amount,8);
                    $data['amount'] = $request->amount;
                } else if (!empty($request->price)) {
                    $data['amount_price'] = $request->price;
                    $data['amount'] = bcdiv($request->price,$ads->price);
                }
                $response =  responseData(true,__('Rate get success'),$data);
            } else {
                $response =  responseData(false,__('Ads not found'));
            }
        } catch(\Exception $e) {
            storeException('getP2pAdsRate ex',$e->getMessage());
        }
        return $response;
    }

    private function checkOrderTime($order)
    {
        try {
            if($order->is_reported == STATUS_PENDING && $order->status == TRADE_STATUS_ESCROW && $order?->payment_time > 0){
                $orderTimeExpire = Carbon::createFromFormat('Y-m-d H:i:s', $order?->payment_expired_time);
                $currentTime = Carbon::now();
                if($currentTime->gte($orderTimeExpire)){
                    return false;
                }
            }
            return true;
        } catch (\Exception $e) {
            storeException('checkTime', $e->getMessage());
            return false;
        }
    }


    public function getP2pOrderDetails($request)
    {
        try {
            $data['order'] = POrder::where('uid',$request->order_uid)->first();
            $data['dispute'] = "";
            $authUser = Auth::user();
            if ($data['order']) {
                $data['due_minute'] = NULL;
                $data['order']->payment_sleep = asset(PAYMENT_SLIP_PATH.($data['order']->payment_sleep ?? ""));
                if(!$this->checkOrderTime($data['order'])){
                    DB::beginTransaction();
                    if(isset($data['order']->sell_id))
                    {
                        $tradeAmount = bcadd($data['order']->amount,$data['order']->buyer_fees,8);
                        $sell_details = PSell::find($data['order']->sell_id);
                        if(! $sell_details) return failed(__('Sell order not found'));
                        $sell_details->increment('available',$tradeAmount);
                        $sell_details->decrement('sold',$tradeAmount);
                        storeException('order canceled refund seller', __('Order is refunded successfully'));
                    }else{
                        $tradeAmount = bcadd($data['order']->amount,$data['order']->buyer_fees,8);
                        $buyerWallet = P2PWallet::find($data['order']->seller_wallet_id);
                        if(! $buyerWallet) return failed(__('Sell order not found'));
                        $buyerWallet->increment('balance',$tradeAmount);
                        storeException('order canceled refund seller', __('Order is refunded successfully'));

                    }
                    $data['order']->status = 0;
                    $data['order']->save();
                    DB::commit();
                    return responseData(false,__('This order is closed due to payment time expire'),$data);
                }

                if ($data['order']->buyer_id == $authUser->id) {
                    $data['user_type'] = TRADE_BUYER;
                    $data['user_buyer'] = $authUser;
                    $data['user_seller'] = User::find($data['order']->seller_id);
                } else {
                    $data['user_type'] = TRADE_SELLER;
                    $data['user_seller'] = $authUser;
                    $data['user_buyer'] = User::find($data['order']->buyer_id);
                }
                if($data['order']->is_reported){
                    $data['dispute'] = POrderDispute::where('order_id',$data['order']->id)
                                        ->where(
                                            fn($q)=>$q
                                            ->where('user_id', authUserId_p2p())
                                            ->orWhere('reported_user', authUserId_p2p())
                                        )->first();
                    $data['who_dispute'] = ($data['user_buyer']->id == $data['dispute']->reported_user) ? __("buyer") : __("seller");
                    $data['dispute']->assigned_admin = $data['dispute']->assigned_admin ? $data['dispute']->admin->first_name.' '.$data['dispute']->admin->last_name: NULL;
                }
                $traderId = $data['order']->buyer_id == $authUser->id ? $data['order']->seller_id : $data['order']->buyer_id;
                $tradeInfoDetails = userTradeInfoDetails($traderId);
                $data['total_trade'] = $tradeInfoDetails['total_trade'];
                $data['completion_rate'] = $tradeInfoDetails['completion_rate_30d'];
                $paymentMethod = BankRecord::getOnlyP2p()->getBankFormRelation()->where('id',$data['order']?->payment_id)->first();
                if ($paymentMethod->bank && isset($paymentMethod->bank)) {
                    $bank = json_decode($paymentMethod->bank, true);
                    if(json_last_error() !== JSON_ERROR_NONE || !is_array($bank)) $bank = [];
                        $paymentMethod->bank = $bank;
                }
                $data['payment_method'] = $paymentMethod;
                if (!empty($data['dispute'])) {
                    $data['chat_messages'] = POrderChat::where('order_id',$data['order']?->id)
                            ->where('dispute_id',$data['dispute']->id)->with(['receiver','user'])->get();
                } else {
                    $data['chat_messages'] = POrderChat::where('order_id',$data['order']?->id)
                            ->where(function($query){
                            return $query
                                    ->where('sender_id', authUserId_p2p())
                                    ->orWhere('receiver_id', authUserId_p2p());
                            })->with(['receiver','user'])->get();
                }

                $data['chat_messages']->map(function($q){
                    $q->file_path = $q->file ? filePathP2P(CONVERSATION_ATTACHMENT_PATH, $q->file) : '';
                    if(isset($q->user)){
                        $q->sender_image_link = isset($q->user->id) ? showUserImageP2P($q->user->id) : '';
                        $q->user_id = $q->user->id ?? '';
                        $q->user['photo'] = showUserImageP2P($q->user_id);;
                    }
                    if(isset($q->receiver)){
                        $q->receiver_image_link = isset($q->receiver->id)? showUserImageP2P($q->receiver->id) : '';
                    }
                });

                $data['current_time'] = Carbon::now()->format('Y-m-d H:i:s');
                $data['due_minute'] = getOrderTimeDiff($data['order']);

                return responseData(true,__('Get details page data succesfully'),$data);
            }
            return responseData(false,__('Order not found'));
        } catch(\Exception $e) {
            storeException('getP2pOrderDetails ex',$e->getMessage());
            return responseData(false,__('Something went wrong'));
        }
    }


    // p2p order payment process
    public function p2pOrderPaymentProcess($request)
    {
        $response =  responseData(false,__('Something went wrong'));
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $order = POrder::where(['uid' => $request->trade_id,'buyer_id' => $user->id, 'status' => TRADE_STATUS_ESCROW])->first();
            if ($order) {
                if ($order->is_reported == STATUS_ACTIVE) {
                    return responseData(false,__('Someone created dispute against this order'));
                }

                if(!$this->checkOrderTime($order))
                return responseData(false,__('Payment time is expired'));

                $slip = "";
                if($request->hasFile('payment_slip')) {
                    $slip = uploadimage($request->payment_slip,PAYMENT_SLIP_PATH);
                }
                $order->update(['payment_sleep' => $slip, 'status' => TRADE_STATUS_PAYMENT_DONE]);
                SendEmailOnChangeState::dispatch(TRADE_STATUS_PAYMENT_DONE, $order)->onQueue("send-mail");
                $this->sendDataViaWebsocket($order->seller_id ,$order->uid , [ 'order' => $order, 'message' => __("Buyer sent payment")]);
                $response =  responseData(true,__('Order payment done'));
            } else {
                $response =  responseData(false,__('Order not found'));
            }
            DB::commit();
        } catch(\Exception $e) {
            DB::rollBack();
            storeException('p2pOrderPaymentProcess ex', $e->getMessage());
        }
        return $response;
    }
    // p2p order release process
    public function p2pOrderReleaseProcess($request)
    {
        $response =  responseData(false,__('Something went wrong'));
        try {
            $user = Auth::user();
            if(empty($request->trade_id)) {
                return responseData(false,__('Order id is required'));
            }
            $order = POrder::where(['uid' => $request->trade_id,'seller_id' => $user->id, 'status' => TRADE_STATUS_PAYMENT_DONE, 'is_queue' => STATUS_INACTIVE])->first();
            if ($order) {
                if ($order->is_reported == STATUS_ACTIVE) {
                    return responseData(false,__('Someone created dispute against this order'));
                }
                $order->update(['is_queue' => STATUS_ACTIVE]);
                ReleaseOrderJob::dispatch($order);
                $request->merge(['order_uid' => $order->uid]);
                $data = $this->getP2pOrderDetails($request)['data'];
                $response =  responseData(true,__('Order released successfully'), $data);
            } else {
                $response =  responseData(false,__('Order not found'));
            }
        } catch(\Exception $e) {
            storeException('p2pOrderPaymentProcess ex', $e->getMessage());
        }
        return $response;
    }
    public function cancelP2pOrder($request)
    {
        try {
            $order = POrder::where('uid',$request->order_uid)->where('buyer_id', authUserId_p2p())->first();
            if($order){
                $order->is_queue = STATUS_ACTIVE;
                $order->save();
                CancelOrderJob::dispatch($order, authUserId_p2p(), $request->reason ?? "");
                SendEmailOnChangeState::dispatch('cancel', $order)->onQueue("send-mail");
                return responseData(true, __('Order canceled successfully'));
            }
            return responseData(false,__('Order not found'));
        } catch(\Exception $e) {
            storeException('cancelP2pOrder ex', $e->getMessage());
            return responseData(false,__('Something went wrong'));
        }
    }

    // check payment validatio


    //check dispute validation
    public function checkDisputeValidation($order_uid)
    {
        $order_details = POrder::where('uid', $order_uid)->first();

        if(isset($order_details))
        {
            if($order_details->status == TRADE_STATUS_PAYMENT_DONE || $order_details->status == TRADE_STATUS_TRANSFER_DONE )
            {

                $user = auth()->user();
                if($order_details->is_reported == 0 )
                {
                    $response = ['success'=>true, 'message'=>__('You can submit a dispute request for this order!'), 'data'=>$order_details];
                }else{

                    if(POrderDispute::where(["order_id" => $order_details->id, "reported_user" => $user->id])->first())//($order_details->is_reported == $user->id)
                    {
                        $response = ['success'=>false , 'message'=> __('You already submited a dispute request!')];
                    }else{
                        if(POrderDispute::where(["order_id" => $order_details->id, "user_id" => $user->id])->first())//($order_details->is_reported == $order_details->buyer_id)
                        {
                            $response = ['success'=>false, 'message'=>__('Buyer already submited a dispute request!')];
                        }else{
                            $response = ['success'=>false, 'message'=>__('Seller already submited a dispute request!')];
                        }
                    }
                }
            }else{
                $response = ['success'=>false, 'message'=> __('You can dispute the order after payment done or transfer done')];
            }
        }else{
            $response = ['success'=>false, 'message'=> __('Order is not found!')];
        }

        return $response;
    }

    public function createDispute($request, $order_details)
    {
        $user = auth()->user();

        $order_details->is_reported = STATUS_ACTIVE;
        $order_details->save();

        $reported_user = ($order_details->buyer_id == $user->id) ? $order_details->seller_id : $order_details->buyer_id;
        $order_dispute = new POrderDispute;
        $order_dispute->uid = pMakeUniqueId();
        $order_dispute->order_id = $order_details->id;
        $order_dispute->user_id = $user->id;
        $order_dispute->reported_user = $reported_user;
        $order_dispute->reason_heading = $request->reason_subject;
        $order_dispute->details = $request->reason_details;

        if ($request->hasFile('image')) {

            $imageName = uploadAnyFileP2P($request->image, PAYMENT_SLIP_PATH);
            $order_dispute->image = $imageName;

        }

        $order_dispute->save();
        SendEmailOnChangeState::dispatch("disput", $order_details)->onQueue("send-mail");
        $this->sendDataViaWebsocket($order_dispute->user_id, $order_details->uid,[ 'order' => $order_details, 'message' => __("This order has been reported")],$order_dispute->reported_user);
        $response = ['success'=>true, 'message'=> __('Dispute request is submitted successfully!')];

        return $response;
    }

    public function getDisputedDetails($uid)
    {
        $order_details = POrder::with(['buyer', 'seller','dispute_details','reported_user'])->where('uid', $uid)->first();

        if(isset($order_details))
        {
            $response = ['success'=>true, 'message'=>__('Dispute order details'), 'data'=> $order_details];
        }else{
            $response = ['success'=>false, 'message'=>__('Invalid Request')];
        }

        return $response;
    }

    public function assignDisputeDetailsToAdmin($request)
    {
        $response = ['success'=>false, 'message'=>__('Something went wrong')];
        try {
            $admin_details = User::where('id', $request->employee_id)->where('role',USER_ROLE_ADMIN)->where('status', STATUS_ACTIVE)->first();

            $dispute_details = POrderDispute::where('uid', $request->dispute_uid)->first();

            if(isset($admin_details) && isset($dispute_details))
            {
                if(isset($dispute_details->assigned_admin))
                {
                    $response = ['success'=>false, 'message'=>__('This is already assigned, You can not re-assigned it!')];
                }else{
                    $dispute_details->assigned_admin = $admin_details->id;
                    $dispute_details->updated_by = auth()->user()->id;
                    $dispute_details->save();
                    POrderChat::where('order_id',$dispute_details->order_id)
                        ->update(['dispute_id' => $dispute_details->id,]);
                    POrderChat::create([
                        'order_id' => $dispute_details->order_id,
                        'dispute_id' => $dispute_details->id,
                        'sender_id' => $admin_details->id,
                        'receiver_id' => $dispute_details->user_id,
                        'message' => 'hello from support, and i am assigned to help you. please co-operate me. thanks'
                    ]);

                    $response = ['success'=>true, 'message'=>__('Dispute details is assigned successfully!')];
                }

            }else{
                $response = ['success'=>false, 'message'=>__('Invalid Request!')];
            }
        } catch(\Exception $e) {
            storeException('assignDisputeDetailsToAdmin ex', $e->getMessage());
        }

        return $response;
    }

    public function releaseDisputeDetailsByAdmin($request)
    {
        $response =  responseData(false,__('Something went wrong'));
        DB::beginTransaction();
        try {
            $user = Auth::user();
            if(empty($request->dispute_uid)) {
                return responseData(false,__('Something is missing!'));
            }
            $dispute_details = POrderDispute::where('uid', $request->dispute_uid)
                                                ->where('assigned_admin', $user->id)->first();

            if(isset($dispute_details))
            {
                $pOrder = POrder::where('id', $dispute_details->order_id)->first();
                ReleaseOrderJob::dispatch($pOrder);
                $pOrder->update(['is_reported' => 0]);
                $dispute_details->update(['status' => 1]);
                $response = ['success'=>true,  'message'=>__('Order is released successfully')];
            }else{
                $response =  responseData(false,__('Dispute Order not found'));
            }

            DB::commit();
        } catch(\Exception $e) {
            DB::rollBack();
            storeException('releaseDisputeDetailsByAdmin ex', $e->getMessage());
        }
        return $response;
    }

    public function refundDisputeDetailsByAdmin($request)
    {
        $response =  responseData(false,__('Something went wrong'));
        DB::beginTransaction();
        try {
            $user = Auth::user();
            if(empty($request->dispute_uid)) {
                return responseData(false,__('Something is missing!'));
            }
            $dispute_details = POrderDispute::where('uid', $request->dispute_uid)
                                                ->where('assigned_admin', $user->id)->first();


            if(isset($dispute_details))
            {
                RefundDisputeOrderJob::dispatch($dispute_details);

                $response = ['success'=>true,  'message'=>__('Order is refunded successfully')];

            }else{
                $response =  responseData(false,__('Dispute Order not found'));
            }

            DB::commit();
        } catch(\Exception $e) {
            DB::rollBack();
            storeException('releaseDisputeDetailsByAdmin ex', $e->getMessage());
        }
        return $response;
    }

    public function getOrderDetails($uid)
    {
        $order_details = POrder::with(['buyer', 'seller','dispute_details','reported_user'])->where('uid', $uid)->first();
        if(isset($order_details))
        {
            $response = responseData(true, __('Order Details'), $order_details);
        }else{
            $response = responseData(false, __('Order Not Found!'));
        }

        return $response;
    }

    public function  getUserTradeDetails($user_id)
    {
        $user_details = User::find($user_id);
        if(isset($user_details))
        {
            $order_list = POrder::where('buyer_id', $user_id)->orWhere('seller_id', $user_id);
            $data['total_trade'] = $order_list->count();
            $data['total_buy_trade'] = $order_list->where('buy_id', $user_id)->count();
            $data['total_sell_trade'] = $order_list->where('sell_id', $user_id)->count();
            $response = responseData(true, __('User Trade Details'), $data);
        }else{
            $response = responseData(false, __('User Not found'));
        }
        return $response;
    }

    private function sendDataViaWebsocket($seller_id, $order_id, $data, $buyer_id = null):void
    {
        try {
            $channel_name1 = 'Order-Status-' . ($buyer_id  ?? authUserId_p2p()) . $order_id;
            $channel_name2 = 'Order-Status-' . $seller_id . $order_id;
            $event_name = 'OrderStatus';
            sendDataThroughWebSocket($channel_name1, $event_name, $data);
            sendDataThroughWebSocket($channel_name2, $event_name, $data);
        } catch (\Exception $e) {
            storeException("sendDataViaWebsocket", $e->getMessage());
        }
    }

    public function myP2pOrder($request)
    {
        try {
            $orderList = [];
            $user = authUser_p2p();
            $orderList = POrder::where('is_reported',STATUS_PENDING)
                ->where(fn($q)=>$q->where('seller_id', $user->id)->orWhere('buyer_id', $user->id));
            if($request->coin != 'all')
                $orderList = $orderList->whereIn("coin_type",explode(',',$request->coin));
            if($request->ads_status != 'all')
                $orderList = $orderList->where("status",$request->ads_status);
            if(isset($request->from_date) && isset($request->to_date))
                $orderList = $orderList->whereBetween('created_at', [date('Y-m-d',strtotime($request->from_date)), date('Y-m-d',strtotime($request->to_date))]);
            $orderList = $orderList->orderBy('created_at','desc')->paginate($request->per_page ?? 10);

            return responseData(true,__("Order get successfully"),$orderList);
        } catch (\Exception $e) {
            storeException("myP2pOrder", $e->getMessage());
            return responseData(false,__("Something went wrong"));
        }
    }

    // my dispute list
    public function myDisputeList($request,$user)
    {
        $response = responseData(false,__("Something went wrong"));
        try {
            $orderList = POrder::where('is_reported',STATUS_ACTIVE)
                ->where(fn($q)=>$q->where('seller_id', $user->id)->orWhere('buyer_id', $user->id));
            if($request->coin != 'all')
                $orderList = $orderList->whereIn("coin_type",explode(',',$request->coin));

            if(isset($request->from_date) && isset($request->to_date))
                $orderList = $orderList->whereBetween('created_at', [date('Y-m-d',strtotime($request->from_date)), date('Y-m-d',strtotime($request->to_date))]);
            $orderList = $orderList->orderBy('created_at','desc')->paginate($request->per_page ?? 10);

            $response = responseData(true,__("Date get success"),$orderList);
        } catch(\Exception $e) {
            storeException("myDisputeList", $e->getMessage());
        }
        return $response;
    }

    public function orderFeedback($request)
    {
        try {
            if($order = POrder::where('uid', $request->order_uid)->first()){
                $updateData = [];
                if($order->seller_id == authUserId_p2p()){
                    if($order->seller_feedback_type !== null) return responseData(false, __("You already given a feedback"));
                    $updateData['seller_feedback_type'] = $request->feedback_type;
                    $updateData['seller_feedback']      = $request->feedback;
                }
                if($order->buyer_id == authUserId_p2p()){
                    if($order->buyer_feedback_type !== null) return responseData(false, __("You already given a feedback"));
                    $updateData['buyer_feedback_type'] = $request->feedback_type;
                    $updateData['buyer_feedback']      = $request->feedback;
                }
                if($order->update($updateData)){
                    SendEmailOnChangeState::dispatch('feedback', $order)->onQueue("send-mail");
                    $this->sendDataViaWebsocket($order->seller_id ,$order->id, $order, $order->buyer_id);
                    return responseData(true, __("Feedback updated successfully"));
                }
                return responseData(false, __("Feedback not updated successfully"));
            } return responseData(false, __("Order not found"));
        } catch (\Exception $e) {
            storeException("orderFeedback", $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }


    public function myOrderListData()
    {
        try {
            $data['coins'] = PCoinSetting::get();
            return responseData(true,__("My order list page data get successfully"),$data);
        } catch(\Exception $e) {
            storeException("myOrderListData p2p", $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

}
