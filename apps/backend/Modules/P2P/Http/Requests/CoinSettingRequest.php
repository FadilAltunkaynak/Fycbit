<?php

namespace Modules\P2P\Http\Requests;

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
            'minimum_price' => 'required|numeric|min:0',
            'maximum_price' => 'required|numeric|min:0|gte:minimum_price',
            'coin_type' => 'required|exists:coins',
            'buy_fees' => 'required|numeric|min:0',
            'sell_fees' => 'required|numeric|min:0',
            'trade_status' => 'required|integer'
        ];
    }

    public function messages()
    {
        return [
            'coin_type.required' => __("Coin type is required"),
            'coin_type.exists' => __("Coin type is invalid"),
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
