<?php

namespace Modules\P2P\Http\Requests\Api;

use App\Model\Coin;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class AdsFilterUserRequest extends FormRequest
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
        $status =  ADS_STATUS_ACTIVE.",".ADS_STATUS_INACTIVE ;
        $coins_type = "BTC";
        Coin::where("status", STATUS_ACTIVE)->get("coin_type")
        ->map(function($row)use(&$coins_type){
            $coins_type .= ",".$row->coin_type;
        });
        return [
            'type' => "required|in:$Buy,$Sell",
            'coin' => "required|in:$coins_type,all",
            'ads_status' => "required|in:$status,all",
            'from_date' => "required_with:to_date|date|before_or_equal:to_date",
            'to_date' => "required_with:from_date|date|after_or_equal:from_date",
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

            'coin.required' => __("Coin is required"),
            'coin.id' => __("Coin is invalid"),

            'ads_status.required' => __("Ads status is required"),
            'ads_status.in' => __("Ads status is invalid"),
            
            'from_date.required_with' => __("From date is Required"),
            'from_date.date' => __("From date is invalid"),
            'from_date.before_or_equal' => __("The from date must be before or equal with :to_date",["to_date" => $this->to_date ?? __("to date")]),

            'to_date.required_with' => __("To date is Required"),
            'to_date.required' => __("To date is invalid"),
            'to_date.after_or_equal' => __("The to date must be after or equal with :from_date",["from_date" => $this->from_date ?? __("form date")]),
        ];
    }
}
