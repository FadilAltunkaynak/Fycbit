<?php

namespace Modules\BlogNews\Http\Requests\Api;

use Illuminate\Http\JsonResponse;


use Illuminate\Foundation\Http\FormRequest;
use Modules\BlogNews\Rules\CheckEncryptValue;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Modules\BlogNews\Rules\CheckEncryptPostValue;

class CommentStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $type = $this->segment(2);
        $this->merge(['type' => $type]);
        $table = $type == 'blog' ? 'blog_posts' : 'news_posts';
        return [
            'name' => 'required|string',
            'email' => 'required|email:rfc,dns',
            'message' => 'required|string',
           'to' => [new CheckEncryptValue($type)],
           'post_id' => "required|exists:$table,slug"
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Name field is required',
            'name.string' => 'Name field must be string',
            
            'email.required' => 'Email field is required',
            'email.email' => 'Email is invalid',
            
            'message.required' => 'Message field is required',
            'message.string' => 'Message field must be string',
            
            'post_id.required' => 'Post field required',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
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
