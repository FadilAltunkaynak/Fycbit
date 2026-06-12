<?php

namespace Modules\IcoLaunchpad\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class IcoBuyPriceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rule = [
            'phase_id' => 'required',
            'token_id' => 'required',
            'payment_method' => 'required',
            'amount' => 'required|numeric|gt:0',
        ];
        if ($this->payment_method == PAYPAL || $this->payment_method == SKRILL || $this->payment_method == STRIPE) {
            $rule['pay_currency'] = 'required';
        }

        if ($this->payment_method == BANK_DEPOSIT) {
            $rule['pay_currency'] = 'required';
        }
        if ($this->payment_method == CRYPTO) {
            $rule['payer_wallet'] = 'required';
        }
        return $rule;
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

    public function messages()
    {
        return [
            'phase_id.required' => __("Phase ID is required"),
            'amount.required' => __("Amount is required"),
            'amount.gt' => __("Amount must be greater than 0")
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
                'success' => false,
                'data' => [],
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
