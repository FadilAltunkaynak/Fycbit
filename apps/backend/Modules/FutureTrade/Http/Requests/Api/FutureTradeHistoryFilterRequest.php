<?php

namespace Modules\FutureTrade\Http\Requests\Api;

use App\Facades\ResponseFacade;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Emum\SortEnum;

class FutureTradeHistoryFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $orderType = implode(',', OrderType::values());
        $sort = implode(',', SortEnum::values());

        return [
            'side' => "in:$orderType",
            // 'time' => 'required_without:time_from|integer|gt:0',
            'time_from' => 'required_with:time_to|date',
            'time_to' => 'required_with:time_from|date|after_or_equal:time_from',
            // 'symbol' => 'required',
            'limit' => 'integer|gt:0',
            'sort' => "in:$sort",
        ];
    }

    public function messages(): array
    {
        return [
            'time.required_without' => __('Time is required'),
            'time.integer' => __('Time is invalid'),
            'time.gt' => __('Time is invalid'),

            'time_from.required_with' => __('Time from is required'),
            'time_from.date' => __('Time from is invalid'),

            'time_to.required_with' => __('Time to is required'),
            'time_to.date' => __('Time to is invalid'),
            'time_to.after_or_equal' => __('Time to must be gather then from time'),

            'side.required' => __('Order side is required'),
            'side.in' => __('Order side is invalid'),

            'symbol.required' => __('Symbol is required'),

            'limit.integer' => __('Limit is invalid'),
            'limit.gt' => __('Limit should be greater than 0'),
            'sort.in' => __('Sort is invalid'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $error = $validator->errors()->all()[0] ?? __('Validation error');
        ResponseFacade::failed($error)->throw();
    }
}
