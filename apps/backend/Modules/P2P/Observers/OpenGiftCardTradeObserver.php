<?php

namespace Modules\P2P\Observers;

use App\User;
use Modules\P2P\Jobs\SendEmail;
use Modules\P2P\Entities\PGiftCard;
use App\Http\Services\MyCommonService;
use Modules\P2P\Entities\PGiftCardOrder;

class OpenGiftCardTradeObserver
{
    /**
     * Handle the Order "created" event.
     *
     * @param  \Modules\P2P\Entities\PGiftCardOrder  $Order
     * @return void
     */
    public function created(PGiftCardOrder $Porder): void
    {
        try {
            $receiver = User::findOrFail($Porder->seller_id);
            $title = __("New gift card order has been placed");
            $body = __("This notification is to inform you that a new gift card order has been placed on your advertisement");
            
            Dispatch(new SendEmail($Porder))->onQueue("send-mail");
            $this->sendNotification($title, $body, $receiver);
        } catch (\Exception $e) {
            storeException('OpenGiftCardTradeObserver create err', $e->getMessage());
        }
    } 

    /**
     * Handle the Order "updated" event.
     *
     * @param  \Modules\P2P\Entities\PGiftCardOrder  $Order
     * @return void
     */
    public function updated(PGiftCardOrder $Order)
    {
        try {
           
        } catch(\Exception $e) {
            storeException('OrderObserver update err', $e->getMessage());
        }
    }

    /**
     * Handle the Order "deleted" event.
     *
     * @param  \Modules\P2P\Entities\PGiftCardOrder  $Order
     * @return void
     */
    public function deleted(PGiftCardOrder $Order)
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     *
     * @param  \Modules\P2P\Entities\PGiftCardOrder  $Order
     * @return void
     */
    public function restored(PGiftCardOrder $Order)
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     *
     * @param  \Modules\P2P\Entities\PGiftCardOrder  $Order
     * @return void
     */
    public function forceDeleted(PGiftCardOrder $Order)
    {
        //
    }
    private function sendNotification($title, $message, $user)
    {
        (new MyCommonService())->sendNotificationToUserUsingSocket(
            $user->id,
            $title,
            $message
        );
    } 
}
