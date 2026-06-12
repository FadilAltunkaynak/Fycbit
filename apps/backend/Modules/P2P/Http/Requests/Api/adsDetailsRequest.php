<?php

namespace Modules\P2P\Http\Requests\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class adsDetailsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $table = $this->ads_type == TRADE_BUY_TYPE ? 'p_buys' : 'p_sells';
        return [
            'ads_type' => 'required|in:'.TRADE_BUY_TYPE.','.TRADE_SELL_TYPE,
            'uid' => "required|exists:$table",
        ];
    }

    public function messages()
    {
        return [
            'ads_type.required' => __("Ads type is required"),
            'ads_type.in' => __("Ads type is invalid"),

            'uid.required' => __("Ads id is required"),
            'uid.exists' => __("Ads id is invalid"),
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
