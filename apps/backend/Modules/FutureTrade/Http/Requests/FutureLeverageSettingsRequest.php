<?php

namespace Modules\FutureTrade\Http\Requests;

use App\Facades\ResponseFacade;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class FutureLeverageSettingsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'coin_pair_uid' => 'required',
            'max_leverage' => 'required|numeric|gt:0',

            'maintenance_amount' => 'required|numeric',
            'min_position_amount' => 'required|numeric',
            'max_position_amount' => 'required|numeric|gt:min_position_amount',

            'maintenance_margin_rate' => 'required|numeric|gt:0',
        ];
    }

    public function messages()
    {
        return [
            'coin_pair_uid.required' => __('Coin pair is required'),
            'max_leverage.required' => __('Max leverage is required'),
            'max_leverage.gt' => __('Max leverage must be greater than zero'),

            'maintenance_amount.required' => __('Maintenance amount is required'),
            'maintenance_amount.numeric' => __('Maintenance amount is invalid'),

            'min_position_amount.required' => __('Min position amount is required'),
            'min_position_amount.numeric' => __('Min position in invalid'),

            'max_position_amount.required' => __('Max position amount is required'),
            'max_position_amount.numeric' => __('Max position amount is invalid'),
            'max_position_amount.gt' => __('Max position amount must be greater than min position amount'),

            'maintenance_margin_rate.required' => __('Maintenance margin rate is required'),
            'maintenance_margin_rate.numeric' => __('Maintenance margin rate is invalid'),
            'maintenance_margin_rate.gt' => __('Maintenance margin rate must be greater than zero'),
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
