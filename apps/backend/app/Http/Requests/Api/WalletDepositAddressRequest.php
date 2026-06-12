<?php

namespace App\Http\Requests\Api;

use App\Facades\ResponseFacade;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class WalletDepositAddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['coin_id'] = "required|numeric|exists:coins,id";

        if ($this->network_id)
            $rules['network_id'] = "required|numeric|exists:networks,id";
        else if ($this->network_type)
            $rules['network_type'] = "required";

        return $rules;
    }

    /**
     * Return validation error custom message
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return [
            'coin_id.required' => __("Coin is required"),
            'coin_id.numeric' => __("Coin is required"),
            'coin_id.exists' => __("Coin is required"),

            'network_id.required' => __("Network is required"),
            'network_id.numeric' => __("Network is required"),
            'network_id.exists' => __("Network is required"),

            'network_type' => __("Network is required"),
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        $error = $validator->errors()->all()[0];
        ResponseFacade::failed($error)->throw();
    }
}
