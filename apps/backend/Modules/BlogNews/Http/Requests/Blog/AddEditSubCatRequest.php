<?php

namespace Modules\BlogNews\Http\Requests\Blog;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class AddEditSubCatRequest extends FormRequest
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
            'category' => ['required',
                Rule::exists('blog_categories','id')->where('sub',0)
            ],
            'status' => 'required|in:1,2',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => __('Title is required'),
            'title.string' => __('Title must be string'),

            'category.required' => __('Category is required'),
            'category.exists' => __('Category is invalid'),

            'status.required' => __('Status is required'),
            'status.in' => __('Status is invalid'),
        ];
    }
}
