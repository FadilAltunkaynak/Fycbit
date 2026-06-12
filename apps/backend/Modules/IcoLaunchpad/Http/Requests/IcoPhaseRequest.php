<?php

namespace Modules\IcoLaunchpad\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class IcoPhaseRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'ico_token_id' => 'required',
            'coin_price' => 'required|numeric|gt:0',
            'coin_currency' => 'required',
            'total_token_supply' => 'required',
            'phase_title' => 'required',
            'description' => 'required',

        ];

        if(isset($this->image))
        {
            $rules['image'] = 'mimes:jpeg,png,jpg,gif|max:2048';
        }
        
        if(isset($this->minimum_purchase_price))
        {
            $rules['minimum_purchase_price'] = 'numeric|min:0';
            $rules['maximum_purchase_price'] = 'required';
        }

        if(isset($this->maximum_purchase_price) && $this->minimum_purchase_price > 0)
        {
            $rules['maximum_purchase_price'] = 'numeric|min:0|gt:minimum_purchase_price';
        }
        
        if(!isset($this->id)) $rules['start_date'] = 'required|date|after_or_equal:' . now()->format('Y-m-d');
        else $rules['start_date'] = 'required|date';
        if (!empty($this->image)) {
            $rules['image'] = 'required|mimes:jpg,png,jpeg,JPG,PNG|max:2048';
        }
        $rules['end_date'] = 'required|date|after_or_equal:start_date';
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
