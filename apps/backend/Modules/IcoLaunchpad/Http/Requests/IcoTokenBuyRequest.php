<?php

namespace Modules\IcoLaunchpad\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class IcoTokenBuyRequest extends FormRequest
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
        $rule = [
            'phase_id' => 'required|exists:ico_phase_infos,id',
            'token_id' => 'required',
            'payment_method' => 'required|exists:currency_deposit_payment_methods,payment_method',
            'amount' => 'required|numeric|gt:0',
        ];
        if ($this->payment_method == PAYPAL || $this->payment_method == SKRILL) {
            $rule['trx_id'] = 'required';
            $rule['pay_currency'] = 'required';
        }
        if ($this->payment_method == STRIPE) {
            $rule['stripe_token'] = 'required';
            $rule['pay_currency'] = 'required';
        }
        if ($this->payment_method == BANK_DEPOSIT) {
            $rule['pay_currency'] = 'required';
            $rule['bank_id'] = 'required';
            $rule['bank_ref'] = 'required';
            $rule['bank_slep'] = 'required';
        }
        if ($this->payment_method == CRYPTO) {
            $rule['payer_wallet'] = 'required';
        }

        if ($this->payment_method == PAYSTACK) {
            $rule['email'] = 'required|email';
        }

        return $rule;
    }



    public function messages()
    {
        return [
            'phase_id.required' => __("Phase is required"),
            'token_id.required' => __("Token is required"),
            'coin_id.required' => __("Coin is required"),
            'amount.required' => __("Amount is required"),
            'amount.numeric' => __("Enter a valid amount"),
            'amount.gt' => __("Amount should be more than 0"),
            'bank_id.required' => __("Select a bank"),
            'bank_ref.required' => __("bank reference is required"),
            'payment_method.required' => __("Select a payment method"),
            'bank_slep.required' => __('Bank Slip is required'),
            'bank_slep.mimes' => __('Bank Slip must be jpeg,jpg,png file'),
            'bank_slep.max' => __('Bank Slip max size is 5000 KB'),
            'trx_id.required' => __('Tx id is required'),
            'stripe_token.required' => __('Srtipe token is required'),
            'payer_wallet.required' => __('Payer wallet is required'),
            'email.required'=>__('Email field is required'),
            'email.email'=>__('Enter a valid mail address '),
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
