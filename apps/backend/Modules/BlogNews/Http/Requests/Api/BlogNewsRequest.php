<?php

namespace Modules\BlogNews\Http\Requests\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class BlogNewsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rule = [
            'type' => 'required_without:category',
            'category' => 'required_without:type|required_with:subcategory',
        ];
        if ($this->segment(2) == 'blog')
            $rule['subcategory'] = 'required_with:category';

        return $rule;
    }

    public function messages()
    {
        return[
            'category.required_without' => 'Category parameter is required',
            'category.required_with' => 'Category parameter is required',
            'type.required_without' => 'Type parameter is required',
            // 'type' => 
            'subcategory.required_with' => 'Subcategory parameter is required',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        //dd($this->header('accept'));
        //if ($this->header('accept') == "application/json") {
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
        // } else {
        //     throw (new ValidationException($validator))
        //         ->errorBag($this->errorBag)
        //         ->redirectTo($this->getRedirectUrl());
        // }

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
