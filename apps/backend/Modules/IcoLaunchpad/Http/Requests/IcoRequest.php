<?php

namespace Modules\IcoLaunchpad\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class IcoRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = Auth::user();

        $rules = [
            'network_id' => 'required',
            'wallet_address' => 'required',
            'contract_address' => 'required',
            'wallet_private_key' => 'required_without:id',
            'gas_limit' => 'required|numeric'
        ];

        if(isset($this->image))
        {
            $rules['image'] = 'mimes:jpeg,png,jpg,gif|max:2048';
        }

        if ($user->role != USER_ROLE_ADMIN || !empty($this->form_id)) {
            $rules['form_id'] = 'required|numeric';
        }

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
        $messages = [
            'image.mimes' => __('Only jpeg,png,jpg,gif image are accepted'),
            'image.max' => __('Image size can not more than 2MB'),
            'network_id.requird' => __('Network is required'),
            'network.requird' => __('Network is required'),
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
