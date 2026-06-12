<?php

namespace Modules\P2P\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;


class TradeDisputeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $extensions = ['png', 'jpg', 'jpeg'];

        $rules = [
            'order_uid'=>'required|exists:p_orders,uid',
            'reason_subject' => 'required|string',
            'reason_details' => 'required|string',
        ];

        if(isset($this->files))
        {
            $rules['image'] = 'mimes:' . implode(',', $extensions);

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
        $extensions = ['png', 'jpg', 'jpeg'];

        return [
            'order_uid.required' => __("Order uid is required"),
            'order_uid.exists' => __("Order is invalid!"),
            'reason_heading.required' => __("Reason subject is required!"),
            'reason_heading.string' => __("Reason subject is must be!"),
            'reason_details.required' => __("Reason details is required!"),
            'reason_details.string' => __("Reason details is must be!"),
            'image.mimes' => __('Only ').implode(',', $extensions). __(' image type is accepted!')
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
}
