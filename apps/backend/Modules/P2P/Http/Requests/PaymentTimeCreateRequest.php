<?php

namespace Modules\P2P\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentTimeCreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'time' => 'required|numeric|gte:1',
            'status' => 'required|in:1,2'
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

    public function messages()
    {
        return [
            'time.required' => __("Time is required"),
            'time.numeric' => __("Time is invalid"),
            'time.gte' => __("Time should be greater than or equal to 1 minute"),

            'status.required' => __("Status is required"),
            'status.in' => __("Status is invalid"),
        ];
    }
}
