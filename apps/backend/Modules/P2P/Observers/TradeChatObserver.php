<?php

namespace Modules\P2P\Observers;

use App\User;
use Modules\P2P\Entities\POrderChat;
use App\Http\Services\MyCommonService;

class TradeChatObserver
{
    /**
     * Handle the Order "created" event.
     *
     * @param  \Modules\P2P\Entities\POrderChat  $Order
     * @return void
     */
    public function created(POrderChat $POrderChat): void
    {
        try {
            $receiver = User::findOrFail($POrderChat?->receiver_id);
            $title = __("You received new message");
            $body = __("This notification is to inform you that a new message has been arrived in trade");
            
            $this->sendEmailAndNotification($title, $body, $receiver);
        } catch (\Exception $e) {
            storeException('OrderChatObserver create err', $e->getMessage());
        }
    }

    /**
     * Handle the Order "updated" event.
     *
     * @param  \Modules\P2P\Entities\POrderChat  $Order
     * @return void
     */
    public function updated(POrderChat $Order)
    {
        //
    }

    /**
     * Handle the Order "deleted" event.
     *
     * @param  \Modules\P2P\Entities\POrderChat  $Order
     * @return void
     */
    public function deleted(POrderChat $Order)
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     *
     * @param  \Modules\P2P\Entities\POrderChat  $Order
     * @return void
     */
    public function restored(POrderChat $Order)
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     *
     * @param  \Modules\P2P\Entities\POrderChat  $Order
     * @return void
     */
    public function forceDeleted(POrderChat $Order)
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
