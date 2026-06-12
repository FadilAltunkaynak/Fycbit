<?php

namespace Modules\P2P\Http\Requests\Api;

use App\Model\CountryList;
use Illuminate\Http\JsonResponse;
use Modules\P2P\Rules\CheckMultiData;
use Modules\P2P\Entities\PPaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class AdsFilterChangeRequest extends FormRequest
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
        return [
            'type' => "required|in:$Buy,$Sell",
            'amount' => "required|gte:0",
            'coin' => "required|exists:coins,coin_type",
            'currency' => "required",
            'payment_method' => ["required",new CheckMultiData(PPaymentMethod::class,"uid", $this->messages()['payment_methods.checkMulti'], true)],
            'country' => ["required",new CheckMultiData(CountryList::class,"key", $this->messages()['countrys.checkMulti'], true)],
        ];
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
            'type.required' => __("Ads Type is required"),
            'type.in' => __("Ads Type is invalid"),

            'amount.required' => __("Amount is required"),
            'amount.gte' => __("Given amount is invalid"),

            'coin.required' => __("Coin is required"),
            'coin.exists' => __("The selected coin is invalid"),
            
            'currency.required' => __("Currency is required"),
            'currency.exists' => __("The selected currency is invalid"),

            'payment_method.required' => __("Paymen method is required"),
            'payment_methods.checkMulti' => __("Payment method is invalid"),
            
            'country.required' => __("Paymen method is required"),
            'countrys.checkMulti' => __("Country is invalid"),
        ];
    }
}
