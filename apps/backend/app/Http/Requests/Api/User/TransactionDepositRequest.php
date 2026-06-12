<?php

namespace App\Http\Requests\Api\User;

use App\Facades\ResponseFacade;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class TransactionDepositRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "coin_id" => "required|numeric|exists:coins,id",
            "network_id" => "required|numeric",
            "trx_id" => "required|string",
        ];
    }

    public function messages()
    {
        return [
            "coin_id.required" => __("Coin is required"),
            "coin_id.numeric" => __("Coin is invalid"),
            "coin_id.exists" => __("Coin not exist"),

            "network_id.required" => __("Network is required"),
            "network_id.numeric" => __("Network is invalid"),

            "trx_id.required" => __("Transaction hash is required"),
            "trx_id.numeric" => __("Transaction hash must be a string"),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $error = $validator->errors()->all()[0];
        ResponseFacade::failed($error)->throw();
    }
}
