<?php

namespace Modules\P2P\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentMethodRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'payment_type' => 'required_without:uid|exists:bank_forms,id',
            'country' => 'required',
            'status' => 'required|in:1,2',
            'logo' => 'image|mimes:jpeg,jpg,png,svg,ico', //required_without:uid|
        ];
    }


    public function messages()
    {
        return [
            'name.required' => __("Payment method name is required"),
            'name.string' => __("Payment method name is invalid"),

            'payment_type.required_without' => __("Payment type is required"),
            'payment_type.exists' => __("Payment type is invalid"),

            'country.required' => __("Country is required"),

            'status.required' => __("Status is required"),
            'status.in' => __("Status is invalid"),

            'logo.required_without' => __("Payment method logo is required"),
            'logo.image' => __("Payment method logo must be jpeg,jpg,png,svg,ico"),
            'logo.mimes' => __("Payment method logo must be jpeg,jpg,png,svg,ico"),
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
}
