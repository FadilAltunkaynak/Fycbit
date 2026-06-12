<?php

namespace Modules\FutureTrade\Http\Requests\Api;

use App\Facades\ResponseFacade;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class FuturePositionHistoryFilterRequest extends FormRequest
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
            // 'time' => 'required_without:time_from|integer|gt:0',
            'time_from' => 'required_with:time_to|date',
            'time_to' => 'required_with:time_from|date|after_or_equal:time_from',
            // 'symbol' => 'required',
            'limit' => 'integer|gt:0',
        ];
    }

    public function messages(): array
    {
        return [
            'coin_pair_uid.required' => __('Coin pair is required'),

            'time.required_without' => __('Time is required'),
            'time.date' => __('Time is invalid'),

            'time_from.required_with' => __('Time from is required'),
            'time_from.date' => __('Time from is invalid'),

            'time_to.required_with' => __('Time to is required'),
            'time_to.date' => __('Time to is invalid'),
            'time_to.after_or_equal' => __('Time to must be gather then from time'),


            'symbol.required' => __('Symbol is required'),

            'limit.integer' => __('Limit is invalid'),
            'limit.gt' => __('Limit should be greater than 0'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $error = $validator->errors()->all()[0];
        ResponseFacade::failed($error)->throw();
    }
}
