<?php

namespace Modules\P2P\Observers;

use App\User;
use App\Model\Wallet;
use App\Jobs\MailSend;
use Modules\P2P\Entities\PBuy;
use Modules\P2P\Entities\PSell;
use Modules\P2P\Entities\POrder;
use Modules\P2P\Entities\POrderChat;
use Modules\P2P\Jobs\SendOrderEmail;
use App\Http\Services\MyCommonService;

class OpenTradeObserver
{
    /**
     * Handle the Order "created" event.
     *
     * @param  \Modules\P2P\Entities\POrder  $Order
     * @return void
     */
    public function created(POrder $Porder): void
    {
        try {
            $user = null;
            $ads_type = TRADE_BUY_TYPE;
            $ads_type_name = __("Buy");
            if(!empty($Porder?->buy_id)){
                $buy = PBuy::where('id', $Porder->buy_id)->with('user')->first();
                $user = $buy->user()->first();
                // if(!empty($buy?->auto_reply)){
                //     $this->sendAutoMessage($Porder, $buy);
                // }
            }
            if(!empty($Porder?->sell_id)){
                $sell = PSell::where('id', $Porder->sell_id)->with('user')->first();
                $user = $sell->user()->first();
                $ads_type = TRADE_SELL_TYPE;
                $ads_type_name = __("Sell");
                // if(!empty($sell?->auto_reply)){
                    // $this->sendAutoMessage($Porder, $sell);
                // }
            }
            $receiver = User::findOrFail(2);
            $title = __("new order has been placed");
            $body = __("This notification is to inform you that a new order has been placed on your :ads ads for :crypto .",
            [
                "ads" => $ads_type_name,
                "crypto" => $Porder?->coin_type,
            ]);
            Dispatch(new SendOrderEmail($Porder))->onQueue('send-mail');
            $this->sendEmailAndNotification($title, $body, $receiver);
        } catch (\Exception $e) {
            storeException('OrderObserver create err', $e->getMessage());
        }
    }

    private function sendAutoMessage($order, $ads,)
    {
        try {
            $data = [
                "sender_id" => $ads?->user_id,
                "receiver_id" => $order?->who_opened,
                "order_id" => $order?->order_id,
                "message" => $ads?->auto_reply,
            ];
            $chat = POrderChat::create($data);
            if(!$chat) storeException("P2p order auto message", "send faild");
        } catch (\Exception $e) {
            storeException("sendAutoMessage", $e->getMessage());
            responseData(false,__("Something went wrong"));
        }
    } 

    /**
     * Handle the Order "updated" event.
     *
     * @param  \Modules\P2P\Entities\POrder  $Order
     * @return void
     */
    public function updated(POrder $Order)
    {
        try {
           
        } catch(\Exception $e) {
            storeException('OrderObserver update err', $e->getMessage());
        }
    }

    /**
     * Handle the Order "deleted" event.
     *
     * @param  \Modules\P2P\Entities\POrder  $Order
     * @return void
     */
    public function deleted(POrder $Order)
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     *
     * @param  \Modules\P2P\Entities\POrder  $Order
     * @return void
     */
    public function restored(POrder $Order)
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     *
     * @param  \Modules\P2P\Entities\POrder  $Order
     * @return void
     */
    public function forceDeleted(POrder $Order)
    {
        //
    }
    private function sendEmailAndNotification($title, $message, $user)
    {
        (new MyCommonService())->sendNotificationToUserUsingSocket(
            $user->id,
            $title,
            $message
        );
    }
}
