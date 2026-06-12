<?php

namespace Modules\P2P\Jobs;

use App\User;
use Illuminate\Bus\Queueable;
use App\Http\Services\MailService;
use Modules\P2P\Entities\PGiftCard;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $order;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $order = $this->order;
            $seller = User::find($order->seller_id);
            $buyer  = User::find($order->buyer_id);
            $card   = PGiftCard::find($order->p_gift_card_id);

            if($seller && $card){
                $template = "p2p::email.place_new_order";
                $subject  = __("New gift card order has been placed");
                $data     = [
                    "email_header"   => __("New gift card order has been placed"),
                    "email_message"  => __("This mail is to inform you that a new gift card order has been placed on your advertisement")
                ];
                $this->sendEmailToUser($template, $subject, $seller, $data);
            }
            
            if($buyer && $card){
                $template = "p2p::email.place_new_order";
                $subject  = __("You successfully placed gift card order");
                $data     = [
                    "email_header"   => __("You successfully placed gift card order"),
                    "email_message"  => __("This mail is to inform you that you successfully placed order for new gift card")
                ];
                $this->sendEmailToUser($template, $subject, $seller, $data);
            }
        } catch (\Exception $e) {
            storeException("sendEmailToUser trait of P2P", $e->getMessage());
        }
    }

    private function sendEmailToUser($template, $subject, $user, $data): void
    {
        try {
            $email    = $user->email;
            $name     = $user->first_name.' '.$user->last_name;
            (new MailService)
                ->send($template, $data, $email, $name, $subject);
        } catch (\Exception $e) {
            storeException("sendEmailToUser trait of P2P", $e->getMessage());
        }
    }
}
