<?php

namespace Modules\FutureTrade\Http\Requests\Api;

use App\Facades\ResponseFacade;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Modules\FutureTrade\Emum\MarginModeEnum;

class FutureLeverageSettingUpdateRequest extends FormRequest
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
            'coin_pair_uid' => 'required',
            'leverage' => "required|integer|gt:0",
        ];
    }

    public function messages(): array
    {
        return [
            'coin_pair_uid.required' => __('Coin pair is required'),

            'leverage.required' => __('Leverage is required'),
            'leverage.integer' => __('Leverage is invalid'),
            'leverage.gt' => __('Leverage can not be 0'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $error = $validator->errors()->all()[0];
        ResponseFacade::failed($error)->throw();
    }
}
