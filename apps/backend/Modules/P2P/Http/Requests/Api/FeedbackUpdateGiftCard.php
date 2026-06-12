<?php

namespace Modules\P2P\Http\Requests\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class FeedbackUpdateGiftCard extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'order_uid' => 'required',
            'feedback' => 'required',
            'feedback_type' => 'required|in:0,1',
        ];
    }
  
    public function messages()
    {
        return [
            'order_uid.required' => __("Order id is required"),

            'feedback.required' => __("Feedback id is required"),

            'feedback_type.required' => __("Feedback type is required"),
            'feedback_type.in' => __("Feedback type is invalid"),
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
}
