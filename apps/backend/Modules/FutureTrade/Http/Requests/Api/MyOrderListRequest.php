<?php

namespace Modules\FutureTrade\Http\Requests\Api;

use App\Facades\ResponseFacade;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Modules\FutureTrade\Emum\OrderMethod;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Emum\SortEnum;

class MyOrderListRequest extends FormRequest
{
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
            'limit' => 'numeric',
            'order_method' => "in:$orderMethod",
            // 'symbol' => 'required',
            'sort' => "in:$sort"
        ];
    }

    public function messages(): array
    {
        return [
            'coin_pair_uid.required' => __('Coin pair is required'),
            'side.required' => __('Order side is required'),
            'side.in' => __('Order side is invalid'),
            'limit.numeric' => __('Limit is invalid'),

            'order_method.required' => __('Order method is required'),
            'order_method.in' => __('Order method is invalid'),

            'to.integer' => __('To is invalid'),
            'to.gt' => __('To should be greater than 0'),

            'symbol.required' => __('Symbol is required'),
            'sort.in' => __('Sort is invalid'),
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

    protected function failedValidation(Validator $validator)
    {
        $error = $validator->errors()->all()[0];
        ResponseFacade::failed($error)->throw();
    }
}
