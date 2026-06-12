<?php

namespace Modules\FutureTrade\Http\Requests\Api;

use App\Facades\ResponseFacade;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class FutureGetCandlesRequest extends FormRequest
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
            'interval' => 'required|integer|gt:0',
            'from' => 'required|integer|gt:0',
            'to' => 'integer|gt:0',
            // 'limit' => 'integer|gt:0',
            // 'offset' => 'integer|gt:0',
        ];
    }

    public function messages(): array
    {
        return [
            'coin_pair_uid.required' => __('Coin pair is required'),

            'from.required' => __('From is required'),
            'from.integer' => __('From is invalid'),
            'from.gt' => __('From should be greater than 0'),

            'interval.required' => __('Interval is required'),
            'interval.integer' => __('Interval is invalid'),
            'interval.gt' => __('Interval should be greater than 0'),

            'to.integer' => __('To is invalid'),
            'to.gt' => __('To should be greater than 0'),

            'offset.integer' => __('Offset is invalid'),
            'offset.gt' => __('Offset should be greater than 0'),

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
