<?php

namespace Modules\DemoTrade\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CoinSettingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'coin_type'          => 'required',
            'faucet_amount'      => 'required|numeric|gt:0',
            'faucet_time'        => 'required|numeric|gte:1',
            'faucet_min_balance' => 'required|numeric|gte:0'
        ];
    }

    public function messages()
    {
        return [
            'faucet_amount.required' => __("Faucet amount is required"),
            'faucet_amount.numeric' => __("Faucet amount must be a number"),
            'faucet_amount.gt' => __("Faucet amount must be greater than 0"),
            
            'faucet_time.required' => __("Faucet time is required"),
            'faucet_time.numeric' => __("Faucet time must be a number"),
            'faucet_time.gte' => __("Faucet time must be greater than or equal to 1"),
            
            'faucet_min_balance.required' => __("Faucet minimum balance is required"),
            'faucet_min_balance.numeric' => __("Faucet minimum balance must be a number"),
            'faucet_min_balance.gte' => __("Faucet minimum balance must be greater than 0"),
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
}
