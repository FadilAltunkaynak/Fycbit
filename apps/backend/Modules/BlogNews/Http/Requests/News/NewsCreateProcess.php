<?php

namespace Modules\BlogNews\Http\Requests\News;

use Illuminate\Http\JsonResponse;
use Modules\BlogNews\Rules\DateCheck;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class NewsCreateProcess extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:100',
            'thumbnail' => 'required_without:slug|image|mimes:jpeg,png,jpg|max:2048',
            'category' =>  'required|exists:news_categories,id',
            'status' => 'required|in:0,1',
            // 'sub_category' => 'required',
            'body' => 'required|string',
            'publish_at' => [ new DateCheck()],
        ];
    }

    public function messages()
    {
        return [
           'title.required' => __('Title is required'),
           'title.string' => __('Title must be string'),
           'title.max' => __('Title length not be greater than 100 characters'),

           'thumbnail.required_without' => __('Thumbnail is required'),
           'thumbnail.image' => __('Thumbnail must be image file'),
           'thumbnail.mimes' => __('Thumbnail image must be jpeg,png,jpg'),

           'category.required' => __('Category is required'),
           'category.exists' => __('Category is invalid'),
           //'category.in' => __('Category is invalid'),

           'status.required' => __('Status is required'),
           'status.in' => __('Status is invalid'),

           //'sub_category.required' => __('Sub category is required'),

           'body.required' => __('Body is required'),
           'body.string' => __('Body must be string'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->header('accept') == "application/json") {
            $errors = [];
            if ($validator->fails()) {
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
            }
            $json = [
                'success'=>false,
                'message' => $errors[0],
            ];
            $response = new JsonResponse($json, 200);

            throw (new ValidationException($validator, $response))->errorBag($this->errorBag)->redirectTo($this->getRedirectUrl());
        } else {
            throw (new ValidationException($validator))
                ->errorBag($this->errorBag)
                ->redirectTo($this->getRedirectUrl());
        }

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
