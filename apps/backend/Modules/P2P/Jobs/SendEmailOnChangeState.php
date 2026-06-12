<?php

namespace Modules\P2P\Jobs;

use App\User;
use Illuminate\Bus\Queueable;
use App\Http\Services\MailService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendEmailOnChangeState implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $status;
    private $order;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($status, $order)
    {
        $this->status = $status;
        $this->order  = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $order     = $this->order;
        $title     = "";
        $body      = "";
        $order_num = $order->order_id;
        $template  = "p2p::email.place_new_order";
        
        if($this->status == TRADE_STATUS_PAYMENT_DONE){
            $title = "Your order state changed";
            $body  = "Your order $order_num state changed to payment done";
        }
        
        if($this->status == "feedback"){
            $title = "Your order got a feedback";
            $body  = "Your order $order_num got a feedback";
        }
 
        if($this->status == "cancel"){
            $title = "Your order has been canceled";
            $body  = "Your order $order_num has been canceled";
        }
      
        if($this->status == TRADE_STATUS_TRANSFER_DONE){
            $title = "Order has been released";
            $body  = "Order $order_num has been released";
        }
        
        if($this->status == "disput"){
            $title = "Order has been reported";
            $body  = "Order $order_num has been reported";
        }

        $data = [
            "email_header"   => $title,
            "email_message"  => $body
        ];

        $seller = User::find($order->seller_id);
        $buyer  = User::find($order->buyer_id);

        $this->sendEmailToUser($template, $title, $buyer, $data);
        $this->sendEmailToUser($template, $title, $seller, $data);
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
