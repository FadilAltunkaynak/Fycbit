<?php

namespace Modules\P2P\Http\Requests\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class UserBlanceTransfer extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $type = WALLET_BALANCE_TRANSFER_SEND.",".WALLET_BALANCE_TRANSFER_RECEIVE;
        return [
            "coin" => "required|exists:coins,coin_type",
            "amount" => "required|numeric|gt:0",
            "type" => "required|in:$type"
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

    public function messages()
    {
        return [
            "coin.required" => __("Coin is required"),
            "coin.exists" => __("Coin is invalid"),
            
            "amount.required" => __("Amount feild is invalid"),
            "amount.numeric" => __("Amount feild is invalid"),
            "amount.gt" => __("Amount must be greater than zero"),
            
            "type.required" => __("Transfer type is required"),
            "type.id" => __("Transfer type is invalid"),
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
}
