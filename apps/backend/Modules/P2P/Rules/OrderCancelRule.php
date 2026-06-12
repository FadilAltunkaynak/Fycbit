<?php

namespace Modules\P2P\Rules;

use Modules\P2P\Entities\POrder;
use Illuminate\Contracts\Validation\Rule;

class OrderCancelRule implements Rule
{
    private $message;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $order = POrder::where('uid',$value)->where('buyer_id', authUserId_p2p())->first();
        if($order){
            if(
                $order->is_reported ||
                $order->status == TRADE_STATUS_CANCELED_TIME_EXPIRED ||
                $order->status == TRADE_STATUS_CANCELED ||
                $order->status == TRADE_STATUS_REFUNDED_BY_ADMIN ||
                $order->status == TRADE_STATUS_RELEASED_BY_ADMIN ||
                $order->status == TRADE_STATUS_PAYMENT_DONE ||
                $order->status == TRADE_STATUS_TRANSFER_DONE
            )  {
                $this->message = __("This trade can't be canceled, it may be reported or no longer in escrowed state");
                return false;
            }
            return true;
        } 
        $this->message = __("No order found or you are not a buyer");
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message ?? __("This trade cancelation is forbidden for you");
    }
}
