<?php

namespace Modules\KnowledgeBase\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ArticleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'category_id'=> 'required',
            'sub_category_id'=> 'required',
            'title'=> 'required|max:150',
            'status'=> 'required',
        ];

        if(isset($this->image))
        {
            $rules['image'] = 'image|max:2048' ;
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'category_id.required' => __("Category field is required"),
            'sub_category_id.required' => __("Sub Category field is required"),
            'title.required' => __("Title field is required"),
            'title.max' => __("Title field can not be more then 150 characters!"),
            'status.required' => __('Status field is required'),
            'image.image' => __('Image must be image')
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
}
