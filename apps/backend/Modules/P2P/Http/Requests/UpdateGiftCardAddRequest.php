<?php

namespace Modules\P2P\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class UpdateGiftCardAddRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'uid' => 'required|exists:p_gift_cards,uid',
            'payment_currency_type' => 'required|in:' . PAYMENT_CURRENCY_FIAT . ',' . PAYMENT_CURRENCY_CRYPTO,
            'currency_type' => 'required|string',
            'price' => 'required|numeric',
            'terms_condition' => 'required|string',
            'country.*' => 'required|string|exists:country_lists,key',
            'time_limit' => 'integer',
            'auto_reply' => 'string',
            'user_registered_before' => 'integer',
            'minimum_coin_hold' => 'numeric|gt:0',
            'payment_method.*' => 'required|string|exists:bank_records,id',
            'status' => 'required|in:' . GIFT_CARD_INACTIVE . ',' . GIFT_CARD_ACTIVE
        ];

        return $rules;
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
            'id.required' => __('Enter gift card ID!'),
            'id.exists' => __('Invalid gift card ID!'),
            'payment_currency_type.required' => __('Enter Payment currency type!'),
            'payment_currency_type.in' => __('Invalid Payment currency type!'),
            'currency_type.required' => __('Enter currency type!'),
            'currency_type.string' => __('Currency type must be string!'),
            'price.required' => __('Enter price!'),
            'price.numeric' => __('Price must be number!'),
            'terms_condition.required' => __("Enter terms and condition!"),
            'terms_condition.string' => __("Terms and condition must be string!"),
            'country.*.required' => __("Enter country!"),
            'country.*.string' => __("Country key must be string!"),
            'country.*.exists' => __("Invalid country!"),
            'time_limit.integer' => __("Time limit must be number!"),
            'auto_reply.string' => __('Auto reply must be string!'),
            'user_registered_before.integer' => __('User account register before must be number!'),
            'minimum_coin_hold.numeric' => __('Minimum Coin holding amount must be number!'),
            'minimum_coin_hold.gt' => __('Minimum Coin holding amount must be grater than 0!'),
            'payment_method.required' => __('Enter payment method!'),
            'payment_method.string' => __('Invalid Payment method!'),
            'status.required' => __('Enter status!'),
            'status.in' => __('Invalid status!'),
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
