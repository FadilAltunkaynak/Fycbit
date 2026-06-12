<?php

namespace Modules\FutureTrade\Http\Requests;

use App\Facades\ResponseFacade;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Modules\FutureTrade\Emum\CollateralTypeEnum;
use Modules\FutureTrade\Emum\FutureCoinPairStatusEnum;

class FutureCoinPairRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'collateral_type' => 'required|in:'.implode(',', CollateralTypeEnum::values()),
            'base_coin_code' => 'required_without:coin_pair_id',
            'trade_coin_code' => 'required_without:coin_pair_id',
            'base_decimal' => 'required|numeric|gt:0',
            'trade_decimal' => 'required|numeric|gt:0',
            'maker_fees_percent' => 'nullable|numeric',
            'taker_fees_percent' => 'nullable|numeric',
            'slippage_percent' => 'nullable|numeric',
            'max_open_orders' => 'nullable|numeric',

            'min_stop_limit_percent' => 'nullable|numeric',
            'max_stop_limit_percent' => 'nullable|numeric|gt:min_stop_limit_percent',

            'min_amount' => 'nullable|numeric',
            'max_amount' => 'nullable|numeric|gt:min_amount',
            'max_leverage' => 'nullable|numeric',
            'funding_rate' => 'nullable|numeric',
            'cap_ratio' => 'nullable|numeric',
            'floor_ratio' => 'nullable|numeric',
            'status' => 'in:'.implode(',', FutureCoinPairStatusEnum::values()),
        ];
    }

    /**
     * Return validation error custom message
     *
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return [
            'collateral_type.required' => __('Select a valid collateral type'),
            'base_coin_code.required_without' => __('Select a base coin'),
            'trade_coin_code.required_without' => __('Select a trade coin'),

            'base_coin_decimal.required' => __('Base coin decimal is required'),
            'base_coin_decimal.numeric' => __('Base coin decimal should be numeric'),
            'base_coin_decimal.gt' => __('Base coin decimal should be greater than 0'),

            'trade_coin_decimal.required' => __('Trade coin decimal is required'),
            'trade_coin_decimal.numeric' => __('Trade coin decimal should be numeric'),
            'trade_coin_decimal.gt' => __('Trade coin decimal should be greater than 0'),

            'maker_fees_percent.numeric' => __('Maker fees percent should be numeric'),
            'taker_fees_percent.numeric' => __('Taker fees percent should be numeric'),
            'slippage_percent.numeric' => __('Market order price max slippage should be numeric'),
            'max_open_order.numeric' => __('Maximum open order should be numeric'),

            'min_stop_limit_percent.numeric' => __('Minimum stop price percent should be numeric'),
            'max_stop_limit_percent.numeric' => __('Maximum stop price percent should be numeric'),
            'max_stop_limit_percent.gt' => __('Maximum stop price percent should be greater than minimum stop price percent'),

            'max_amount.numeric' => __('Maximum amount should be numeric'),
            'max_amount.gt' => __('Maximum amount should be greater than minimum amount'),

            'min_amount.numeric' => __('Minimum amount should be numeric'),
            'max_leverage.numeric' => __('Maximum leverage should be numeric'),
            'funding_rate.numeric' => __('Funding rate should be numeric'),
            'cap_ratio.numeric' => __('Cap ratio should be numeric'),
            'floor_ratio.numeric' => __('Floor ratio should be numeric'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $error = $validator->errors()->all()[0];
        ResponseFacade::failed($error)->throw();
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
