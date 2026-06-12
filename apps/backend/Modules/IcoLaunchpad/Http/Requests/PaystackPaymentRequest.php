<?php

namespace Modules\IcoLaunchpad\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PaystackPaymentRequest extends FormRequest
{
    public function rules()
    {
        $rules = [
            'email'=>'required|email',
            'amount'=>'required|numeric|gt:0',
            'phase_id' => 'required|exists:ico_phase_infos,id',
            'token_id' => 'required',
            'payment_method' => 'required|exists:currency_deposit_payment_methods,payment_method'
            
        ];
        return $rules;
    }

    public function messages()
    {
        $messages=[
            'email.required'=>__('Email field is required'),
            'email.email'=>__('Enter a valid mail address '),
            'amount.required'=>__('Amount field is required'),
            'amount.numeric'=>__('Amount must be Number'),
            'phase_id.required' => __("Phase is required"),
            'token_id.required' => __("Token is required"),
            'payment_method.required' => __("Select a payment method"),
        ];

        return $messages;
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
