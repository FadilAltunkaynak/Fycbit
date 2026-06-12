<?php

namespace Modules\FutureTrade\Http\Requests\Api;

use App\Facades\ResponseFacade;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Modules\FutureTrade\Emum\OrderMethod;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Emum\SortEnum;

class FutureOrderHistoryFilterRequest extends FormRequest
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
        $sort = implode(',', SortEnum::values());
        return [
            'side' => "in:$orderType",
            'time' => 'integer|gt:0',
            'time_from' => 'date',
            'time_to' => 'date|after_or_equal:time_from',
            'order_method' => "in:$orderMethod",
            // 'symbol' => 'required',
            'limit' => 'gt:0',
            'sort' => "in:$sort"
        ];
    }

    public function messages(): array
    {
        return [
            'time.required_without' => __('Time is required'),
            'time.date' => __('Time is invalid'),

            'time_from.required_with' => __('Time from is required'),
            'time_from.date' => __('Time from is invalid'),

            'time_to.required_with' => __('Time to is required'),
            'time_to.date' => __('Time to is invalid'),
            'time_to.after_or_equal' => __('Time to must be gather then from time'),

            'side.required' => __('Order method is required'),
            'side.in' => __('Order method is invalid'),

            'order_method.required' => __('Order method is required'),
            'order_method.in' => __('Order method is invalid'),

            'symbol.required' => __('Symbol is required'),

            'limit.integer' => __('Limit is invalid'),
            'limit.gt' => __('Limit should be greater than 0'),
            'sort.in' => __('Sort is invalid'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $error = $validator->errors()->all()[0];
        ResponseFacade::failed($error)->throw();
    }
}
