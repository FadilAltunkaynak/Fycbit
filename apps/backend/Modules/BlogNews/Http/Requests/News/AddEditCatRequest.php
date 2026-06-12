<?php

namespace Modules\BlogNews\Http\Requests\News;

use Illuminate\Foundation\Http\FormRequest;

class AddEditCatRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string',
            'status' => 'required|in:1,2',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => __('Title is required'),
            'title.string' => __('Title must be string'),

            'status.required' => __('Status is required'),
            'status.in' => __('Status is invalid'),
        ];
    }
}
