<?php

namespace Modules\FutureTrade\Http\Requests\Api;

use App\Facades\ResponseFacade;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Modules\FutureTrade\Emum\MarginModeEnum;
use Modules\FutureTrade\Emum\OrderMethod;
use Modules\FutureTrade\Emum\OrderType;

class FutureOrderRequest extends FormRequest
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
        $orderType = implode(',', OrderType::values());
        $orderMethod = implode(',', OrderMethod::values());
        $stopLimit = OrderMethod::STOP_LIMIT->value;

        return [
            'coin_pair_uid' => 'required',
            'amount' => 'required_without:is_reduce|numeric|gt:0',
            'price' => 'required_unless:order_method,2|numeric',

            'order_type' => "required|in:$orderType",
            'stop_price' => "required_if:order_method,$stopLimit|numeric",
            'is_close' => 'nullable',

            // 'mark_price' => 'required|numeric',
            // 'index_price' => 'required|numeric',
            // 'market_price' => 'required|numeric',

            'is_reduce' => 'nullable',
            'order_method' => "required|in:$orderMethod",
            'take_profit_price' => 'nullable|numeric',
            'stop_loss_price' => 'nullable|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'coin_pair_uid.required' => __('Coin pair is required'),
            'order_type.required' => __('Order Type is required'),
            'order_type.in' => __('Order Type is invalid'),

            'margin_mode.required' => __('Margin mode is required'),
            'margin_mode.in' => __('Margin mode is invalid'),

            'amount.required_without' => __('Amount is required'),
            'amount.numeric' => __('Amount is invalid'),
            'amount.gt' => __('Amount should be greater than 0'),

            'price.required_unless' => __('Price is required'),
            'price.numeric' => __('Price is invalid'),

            'stop_price.required_if' => __('Stop price is required'),
            'stop_price.numeric' => __('Stop price is invalid'),

            'mark_price.required' => __('Mark price is required'),
            'mark_price.numeric' => __('Mark price is invalid'),

            'market_price.required' => __('Market price is required'),
            'market_price.numeric' => __('Market price is invalid'),

            'index_price.required' => __('Index price is required'),
            'index_price.numeric' => __('Index price is invalid'),

            'take_profit_price.numeric' => __('Take-Profit price is invalid'),
            'stop_loss_price.numeric' => __('Stop-Loss price is invalid'),

            'order_method.required' => __('Order method is required'),
            'order_method.in' => __('Order method is invalid'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $error = $validator->errors()->all()[0];
        ResponseFacade::failed($error)->throw();
    }
}
