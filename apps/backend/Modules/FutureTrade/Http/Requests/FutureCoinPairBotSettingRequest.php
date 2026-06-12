<?php

namespace Modules\FutureTrade\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FutureCoinPairBotSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bot_amount_low' => 'required|numeric|gt:0',
            'bot_amount_high' => 'nullable|required_with:bot_amount_low|numeric|gte:bot_amount_low',
            'bot_price_low' => 'nullable|numeric|gte:0',
            'bot_price_high' => 'nullable|required_with:bot_price_low|numeric|gte:bot_price_low',
            'bot_order_interval' => 'required|integer|min:1',
            'bot_status' => 'required|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'bot_amount_low.required' => __('Amount Low is required'),
            'bot_amount_low.numeric' => __('Amount Low must be numeric'),
            'bot_amount_low.gt' => __('Amount Low must be greater than 0'),
            'bot_amount_high.required' => __('Amount High is required'),
            'bot_amount_high.numeric' => __('Amount High must be numeric'),
            'bot_amount_high.gte' => __('Amount High must be greater than or equal to Amount Low'),
            'bot_price_low.required' => __('Price Low is required'),
            'bot_price_low.numeric' => __('Price Low must be numeric'),
            'bot_price_low.gte' => __('Price Low must be greater than or equal to zero'),
            'bot_price_high.required' => __('Price High is required'),
            'bot_price_high.numeric' => __('Price High must be numeric'),
            'bot_price_high.gte' => __('Price High must be greater than or equal to Price Low'),
            'bot_order_interval.required' => __('Order Place Time Interval is required'),
            'bot_order_interval.integer' => __('Order Place Time Interval must be an integer'),
            'bot_order_interval.min' => __('Order Place Time Interval must be at least 1 second'),
            'bot_status.required' => __('Status is required'),
            'bot_status.in' => __('Status is invalid'),
        ];
    }
}
