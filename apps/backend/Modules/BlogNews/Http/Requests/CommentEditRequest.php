<?php

namespace Modules\BlogNews\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentEditRequest extends FormRequest
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
            'email' => 'required|email:rfc,dns',
            'message' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('Name field is required'),
            'name.string' => __('Name field must be string'),

            'email.required' => __('Email field is required'),
            'email.email' => __('Email is invalid'),

            'message.required' => __('Message field is required')
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
