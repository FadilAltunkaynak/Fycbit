<?php

namespace Modules\FutureTrade\Http\Requests\Api;

use App\Facades\ResponseFacade;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Modules\FutureTrade\Emum\MarginModeEnum;

class FutureMarginModeUpdateRequest extends FormRequest
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
        $marginMode = implode(',', MarginModeEnum::values());
        return [
            'coin_pair_uid' => 'required',
            'margin_mode' => "required|in:$marginMode",
        ];
    }

    public function messages(): array
    {
        return [
            'coin_pair_uid.required' => __('Coin pair is required'),

            'margin_mode.required' => __('Margin mode is required'),
            'margin_mode.in' => __('Margin mode is invalid'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $error = $validator->errors()->all()[0];
        ResponseFacade::failed($error)->throw();
    }
}
