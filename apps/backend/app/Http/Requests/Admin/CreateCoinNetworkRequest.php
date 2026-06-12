<?php

namespace App\Http\Requests\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCoinNetworkRequest extends FormRequest
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
        $rule = [
            "network_id" => "required_without:uid",
            "currency_id" => "required_without:uid",
            "type" => "required|in:1,2",
            "contract_address" => "required_if:type,1",
            "withdrawal_fees" => "numeric",
            'coin_decimal' => "nullable|int|min:0",
            'base_type' => "required"
        ];
        if ($this->contract_address) {
            $rule['contract_address'] = ['max:255',Rule::unique('coin_networks')->ignore($this->uid,'uid','contract_address')];
        }
        return $rule;
    }

    public function messages(){
        return [
            "network_id.required_without" => __("Network is required"),
            "network_id.exists" => __("Network not found"),

            "currency_id.required_without" => __("Currency is required"),
            "currency_id.exists" => __("Currency not found"),

            "type.required" => __("Coin network type is required"),
            "type.in" => __("Coin network type is invalid"),

            "contract_address.required_if" => __("Contract address is required"),
            "withdrawal_fees.numeric" => __("Withdrawal fees invalid"),
            "coin_decimal.required" => __("Decimal is required"),
            "coin_decimal.int" => __("Decimal must be integer"),
            "coin_decimal.min" => __("Decimal places must be non negative"),
        ];
    }
}
