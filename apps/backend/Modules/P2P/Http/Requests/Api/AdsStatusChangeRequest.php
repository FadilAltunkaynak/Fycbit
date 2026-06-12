<?php

namespace Modules\P2P\Http\Requests\Api;

use Modules\P2P\Entities\PBuy;
use Modules\P2P\Rules\EqualTo;
use Modules\P2P\Entities\PSell;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class AdsStatusChangeRequest extends FormRequest
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
        $Table = isset($this->type) && $this->type == $Buy ? 'p_buys' : 'p_sells';
        $TableClass = isset($this->type) && $this->type == $Buy ? PBuy::class : PSell::class;
        return [
            'type' => "required|in:$Buy,$Sell",
            'id' => ["required","exists:$Table,uid",new EqualTo($TableClass,'=','uid',['user_id',authUserId_p2p()],$this->messages()['user_id.EqualTo'])],
            // 'status' => "required|in:0,1"
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

            'id.required' => __("Ads id is required"),
            'id.exists' => __("Ads is invalid"),

            // 'status.required' => __("Ads type is required"),
            // 'status.in' => __("Ads type is invalid"),

            'user_id.EqualTo' => __("You are not allowed to change this Ads status")
        ];
    }
}
