<?php

namespace Modules\P2P\Rules;

use Modules\P2P\Entities\POrder;
use Illuminate\Contracts\Validation\Rule;

class OrderDetailsRule implements Rule
{
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
        $order = POrder::where('uid',$value)->where(function($query){
            return $query
                  ->where('buyer_id', authUserId_p2p())
                  ->orWhere('seller_id', authUserId_p2p());
        })->first();
        if($order) return true;
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __("This trade is forbidden for you");
    }
}
