<?php

namespace Modules\P2P\Jobs;

use App\User;
use Illuminate\Bus\Queueable;
use Modules\P2P\Entities\POrder;
use App\Http\Services\MailService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendOrderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $order;
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

            if($seller){
                $template = "p2p::email.place_new_order";
                $subject  = __("New order has been placed");
                $data     = [
                    "email_header"   => __("New order has been placed"),
                    "email_message"  => __("This mail is to inform you that a new order :order has been placed on your advertisement", [
                        "order" => $order->order_id
                    ])
                ];
                $this->sendEmailToUser($template, $subject, $seller, $data);
            }
            
            if($buyer){
                $template = "p2p::email.place_new_order";
                $subject  = __("You successfully placed order");
                $data     = [
                    "email_header"   => __("You successfully placed order"),
                    "email_message"  => __("This mail is to inform you that you successfully placed order :order", [
                        "order" => $order->order_id
                    ])
                ];
                $this->sendEmailToUser($template, $subject, $buyer, $data);
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
