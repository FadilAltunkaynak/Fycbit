<?php

namespace App\Http\Requests\Api;

use App\Enums\FundEnum;
use App\Facades\ResponseFacade;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class FundTransferSubmitRequest extends FormRequest
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
        return [
            "coin_type" => "required",
            "amount"    => "required|numeric|gt:0",
            "fund_from" => "required|in:". implode(',', FundEnum::values()),
            "fund_to"   => "required|in:". implode(',', FundEnum::values()),
        ];
    }

        /**
     * Return validation error custom message
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return [
            "coin_type.required" => __("Coin type is required"),

            "amount.required" => __("Amount is required"),
            "amount.numeric"  => __("Amount is invalid"),
            "amount.gt"       => __("Amount should be greater than zero"),

            "fund_from.required" => __("Fund transfer from should select one option"),
            "fund_from.in"       => __("Fund transfer from is invalid"),

            "fund_to.required" => __("Fund transfer to should select one option"),
            "fund_to.in"       => __("Fund transfer to is invalid"),
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        $error = $validator->errors()->all()[0];
        ResponseFacade::failed($error)->throw();
    }
}
