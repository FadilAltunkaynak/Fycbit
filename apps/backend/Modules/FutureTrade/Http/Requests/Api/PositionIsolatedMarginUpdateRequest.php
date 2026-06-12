<?php

namespace Modules\FutureTrade\Http\Requests\Api;

use App\Facades\ResponseFacade;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Modules\FutureTrade\Emum\IsolatedMarginUpdateActionEnum;
use Modules\FutureTrade\Emum\MarginModeEnum;

class PositionIsolatedMarginUpdateRequest extends FormRequest
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
        $actions = implode(',', IsolatedMarginUpdateActionEnum::values());
        return [
            'coin_pair_uid' => 'required',
            'amount' => 'required_without:is_reduce|numeric|gt:0',
            'action' => "required|in:$actions",
        ];
    }

    public function messages(): array
    {
        return [
            'coin_pair_uid.required' => __('Coin pair is required'),
            'action.required' => __('Action is required'),
            'action.in' => __('Action is invalid'),

            'amount.required_without' => __('Amount is required'),
            'amount.numeric' => __('Amount is invalid'),
            'amount.gt' => __('Amount should be greater than 0'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $error = $validator->errors()->all()[0];
        ResponseFacade::failed($error)->throw();
    }
}
