<?php

namespace Modules\FutureTrade\Http\Requests\Api;

use App\Facades\ResponseFacade;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Modules\FutureTrade\Emum\MarginModeEnum;

class PositionTpSlUpdateRequest extends FormRequest
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
            'position_uid' => 'required',
            'tp_price' => 'required_without:sl_price|numeric|gte:0',
            'sl_price' => 'required_without:tp_price|numeric|gte:0',
        ];
    }

    public function messages(): array
    {
        return [
            'position_uid.required' => __('Position is required'),

            'tp_price.required_without' => __('Take profit price is required'),
            'tp_price.numeric' => __('Take profit price is invalid'),
            'tp_price.gt' => __('Take profit price should be greater or equal than 0'),

            'sl_price.required_without' => __('Stop loss price is required'),
            'sl_price.numeric' => __('Stop loss price is invalid'),
            'sl_price.gt' => __('Stop loss price should be greater or equal than 0'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $error = $validator->errors()->all()[0];
        ResponseFacade::failed($error)->throw();
    }
}
