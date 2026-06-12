<?php

namespace Modules\P2P\Http\Requests;

use Illuminate\Http\JsonResponse;
use Modules\P2P\Rules\OrderCancelRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class OrderCancelRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'order_uid' => ["required","exists:p_orders,uid", new OrderCancelRule() ],
            'reason' => "required"
        ];
    }

    public function messages()
    {
        return [
            'order_uid.required' => __("Trade id is required"),
            'order_uid.exists' => __("Invalid trade"),

            'reason.required' => __("Order cancel reason is required")
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
