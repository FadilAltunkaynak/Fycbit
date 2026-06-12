<?php

namespace Modules\P2P\Http\Requests\Api;

use Modules\P2P\Rules\EqualTo;
use Modules\P2P\Entities\POrder;
use Illuminate\Http\JsonResponse;
use Modules\P2P\Rules\CallBackRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class FeedBackRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $pssitive = FEEDBACK_TYPE_POSITIVE;
        $negative = FEEDBACK_TYPE_NEGATIVE;
        return [
            'feedback_type' => "required|in:$pssitive,$negative",
            'feedback' => "required",
            'order_uid' => ['required','exists:p_orders,uid', new CallBackRule($this, "checkOrderToFeedback", $this->messages()['order_uid.CallBackRule'])],
        ];
    }



    protected function failedValidation(Validator $validator)
    {
        $errors = [];
        if ($validator->fails()) {
            $e = $validator->errors()->all();
            foreach ($e as $error) {
                $errors[] = $error;
            }
        }
        $json = [
            'success'=>false,
            'message' => $errors[0],
        ];
        $response = new JsonResponse($json, 200);
        throw (new ValidationException($validator, $response))->errorBag($this->errorBag)->redirectTo($this->getRedirectUrl());
    }

    public function messages()
    {
        return [
            'feedback_type.required' => __("Feedback type is required"),
            'feedback_type.id' => __("Feedback type is invalid"),

            'feedback.required' => __("Feedback cannot be empty"),

            'order_uid.required' => __("Order is required"),
            'order_uid.exists'   => __("Order is invalid"),
            'order_uid.CallBackRule'  => __("You are not allowed to give feedback to this order"),
        ];
    }

    public function checkOrderToFeedback()
    {
        $order = POrder::where('uid', $this->order_uid)->where('status', TRADE_STATUS_TRANSFER_DONE)->where(fn($query)=>
            $query->where('seller_id', authUserId_p2p())->orWhere('buyer_id', authUserId_p2p())
        )->first();
        if($order){
            if($order->seller_id == authUserId_p2p() && $order->seller_feedback_type != NULL){
                CallBackRule::$returnMessage = __("You can not update your feedback");
                return 0;
            }
            if($order->buyer_id == authUserId_p2p() && $order->buyer_feedback_type != NULL){
                CallBackRule::$returnMessage = __("You can not update your feedback");
                return 0;
            }
            return 1;
        } 
        CallBackRule::$returnMessage = __("Order not found");
        return 0;
    }


    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
