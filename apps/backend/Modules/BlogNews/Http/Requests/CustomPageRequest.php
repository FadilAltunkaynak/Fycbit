<?php

namespace Modules\BlogNews\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomPageRequest extends FormRequest
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

    public function rules()
    {
        return [
            'title' => 'required|string',
            'status' => 'required|in:0,1',
            'body' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => __("Title field is required"),
            'title.string' => __("Title must be string"),

            'status.required' => __("Status field is required"),
            'status.id' => __("Status is invalid"),

            'body.required' => __("Body field is required"),
        ];
    } 
}
