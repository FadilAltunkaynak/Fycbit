<?php

namespace Modules\FutureTrade\Http\Requests\Api;

use App\Facades\ResponseFacade;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Modules\FutureTrade\Emum\OrderMethod;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Emum\SortEnum;

class FutureOrderCancelRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $orderType = implode(',', OrderType::values());
        return [
            "order_uid" => 'required',
            'order_type' => "required|in:$orderType",
        ];
    }

    public function messages(): array
    {
        return [
            'order_uid.required' => __('Order uid is required'),
            'order_type.required' => __('Order type is required'),
            'order_type.in' => __('Order type is invalid'),
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
