<?php

namespace Modules\P2P\Http\Requests\Api;

use App\Model\DynamicBank\BankRecord;
use Modules\P2P\Entities\PBuy;
use Modules\P2P\Entities\PSell;
use Illuminate\Http\JsonResponse;
use Modules\P2P\Rules\CheckMultiData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;


class P2pOrderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $Buy = TRADE_BUY_TYPE;
        $Sell = TRADE_SELL_TYPE;
        $offer = "";
        $rules = [
            'ads_type' => "required|in:$Buy,$Sell",
            'ads_id' => "required",
            'payment_id' => "required",
        ];
        if (!empty($this->ads_id) && !empty($this->ads_type)) {
            if($this->ads_type == $Buy) {
                $rules['ads_id'] = 'exists:p_sells,uid';
                $offer = PSell::where('uid',$this->ads_id)->first();
            } else {
                $rules['ads_id'] = 'exists:p_buys,uid';
                $offer = PBuy::where('uid',$this->ads_id)->first();
            }
        }
        if (!empty($offer)) {
            $rules['payment_id'] = ["required",new CheckMultiData(BankRecord::class,"id", $this->messages()['payment_id.checkMulti'])];
        }
        if(empty($this->amount) && (empty($this->price))) {
            $rules['amount'] = "required";
        }
        if (!empty($this->amount)) {
            $rules['amount'] = "numeric|gt:0";
        }
        if (!empty($this->price)) {
            $rules['price'] = "numeric|gt:0";
            // if(!empty($offer)) {
            //     $rules['price'] = "min:$offer->minimum_trade_size|max:$offer->maximum_trade_size";
            // }
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'ads_id.required' => __("Ads id is required"),
            'ads_id.exists' => __("Invalid ad id"),

            'ads_type.required' => __("Ads Type is required"),
            'ads_type.in' => __("Ads Type is invalid"),

            'coin_type.required' => __("Asset is required"),
            'coin_type.exists' => __("Asset is invalid"),

            'payment_id.required' => __("Payment method is required"),
            'payment_id.checkMulti' => __("Invalid payment method or not found that you selected"),

            'price.numeric' => __("Price field is invalid"),
            'price.gt' => __("Price can not be less than 0"),

            'amount.required' => __("You must input crypto or fiat amount"),
            'amount.numeric' => __("Amount field is invalid"),
            'amount.gt' => __("Amount can not be less than 0"),
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
