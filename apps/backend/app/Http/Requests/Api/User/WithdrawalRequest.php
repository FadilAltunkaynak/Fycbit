<?php

namespace App\Http\Requests\Api\User;

use App\Model\Coin;
use App\Model\Wallet;
use App\Model\CoinSetting;
use App\Facades\ResponseFacade;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use App\Http\Repositories\CoinSettingRepository;

class WithdrawalRequest extends FormRequest
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
        $rules = [
            'coin_id' => 'required|exists:coins,id',
            'address' => 'required|string',
        ];

        if ($this->network_id)
            $rules['network_id'] = "required|numeric|exists:networks,id";
        else if ($this->network_type)
            $rules['network_type'] = "required|string";

        $settings = settings();
        if (
            filter_var(
                $settings["two_factor_withdraw"],
                FILTER_VALIDATE_BOOLEAN
            )
            && !defined("IS_PUBLIC_API")
            && !Route::is("userPreWithdrawalProcess")
        )   $rules['code'] = ['required'];

        $coin = Coin::find($this->coin_id);

        if ($coin) {
            $rules['amount'] = 'required|numeric|min:' . $coin->minimum_withdrawal . '|max:' . $coin->maximum_withdrawal;

            if (!empty($this->note)) {
                $rules['note'] = 'string';
            }
        }

        return $rules;
    }

    public function messages()
    {
        $msg = [
            'coin_id' => __("Coin is required"),
            'network_id' => __("Network is required"),
            'network_type' => __("Network is required"),

            'address.required' => __('Address is required'),
            'address.string' => __('Address must be a string!'),

            'amount.required' => __('Amount is required'),
            'amount.numeric' => __('Amount must be numeric field!'),

            'note.string' => __('Message must be a string'),

            'code.required' => __('Code is required'),
        ];

        return $msg;
    }

    protected function failedValidation(Validator $validator)
    {
        $error = $validator->errors()->all()[0];
        ResponseFacade::failed($error)->throw();
    }
}
