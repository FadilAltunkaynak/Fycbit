<?php

namespace Modules\BlogNews\Http\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;

class AddEditCatRequest extends FormRequest
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
