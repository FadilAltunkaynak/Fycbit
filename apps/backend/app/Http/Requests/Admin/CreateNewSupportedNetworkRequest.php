<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CreateNewSupportedNetworkRequest extends FormRequest
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
        return [
            "name"           => "required",
            "type"           => "required|in:".implode(",", array_keys(getBaseNetworkType())),
            "environment"    => "required|in:".implode(",", array_keys(getNetworkEnvironment())),
            "chain_id"       => "required|numeric|gt:0",
            "native_currency"=> "required",
            "base_url"       => "required|regex:%^https:([/]{2})([a-zA-Z0-9]+)([\.])([a-zA-Z0-9])\w+%i",
            "token_endpoint" => "required",
            "address_endpoint"=> "required",
            "tx_endpoint"    => "required",
            "gas_limit"      => "required|numeric|gt:0",
            "gas_price"      => "required|numeric|gt:0",
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __("Network name is required"),
            'native_currency.required' => __("Network native currency is required"),
            'token_endpoint.required'  => __("Network token endpoint is required"),
            'address_endpoint.required'=> __("Network address endpoint is required"),
            'tx_endpoint.required'     => __("Network transaction endpoint is required"),

            'type.required' => __("Network type is required"),
            'type.in'       => __("Network type is invalid"),
            
            'environment.required' => __("Network environment is required"),
            'environment.in'       => __("Network environment is invalid"),

            'chain_id.required' => __("Network chain id required"),
            'chain_id.numeric'  => __("Network chain id should be a number"),
            'chain_id.gt'       => __("Network chain id should be greater then zero"),
            
            'gas_limit.required' => __("Network gas limit required"),
            'gas_limit.numeric'  => __("Network gas limit should be a number"),
            'gas_limit.gt'       => __("Network gas limit should be greater then zero"),
            
            'gas_price.required' => __("Network gas price required"),
            'gas_price.numeric'  => __("Network gas price should be a number"),
            'gas_price.gt'       => __("Network gas price should be greater then zero"),
            
            'base_url.required' => __("Network explorer url is required"),
            'base_url.regex'    => __("Network explorer url is invalid"),
        ];
    }
}
