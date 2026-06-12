<?php

namespace Modules\P2P\Http\Requests\Api;

use Modules\P2P\Entities\PBuy;
use Modules\P2P\Entities\PSell;
use Illuminate\Http\JsonResponse;
use Modules\P2P\Entities\P2PWallet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class AvailableBalanceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $Buy = TRADE_BUY_TYPE;
        $sell = TRADE_SELL_TYPE;
        $table = $this->type == TRADE_BUY_TYPE ? 'p_buys' : 'p_sells';
        if ($this->type == TRADE_BUY_TYPE) $this->merge(['model' => PBuy::class]);
        else $this->merge(['model' => PSell::class]);
        if (!isset($this->uid)) $this->merge(['model' => P2PWallet::class]);
        return [
            'type' => "required|in:$Buy,$sell",
            'uid' => "sometimes|exists:$table",
            'coin_type' => 'required|exists:coins',
        ];
    }

    public function messages()
    {
        return [
            'type.required' => __("Ads type is required"),
            'type.in' => __("Ads type is invalid"),

            'uid.exists' => __("Ads is invalid"),

            'coin_type.required' => __("Asset is required"),
            'coin_type.exists' => __("Asset is invalid"),
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
}
