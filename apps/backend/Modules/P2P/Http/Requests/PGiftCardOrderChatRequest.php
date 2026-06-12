<?php

namespace Modules\P2P\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class PGiftCardOrderChatRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $extensions = ['png', 'jpg', 'jpeg', 'gif'];
        $rules = [
            'gift_card_order_id' => 'required|exists:p_gift_card_orders,id',
            //'message'=>'string'
        ];

        if(isset($this->file))
        {
            $rules['file'] = 'mimes:' . implode(',', $extensions);
            
        }else{
            $rules['message'] = 'required';
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
        $extensions = ['png', 'jpg', 'jpeg', 'gif'];
        return [
            'gift_card_order_id.required'=> __('Enter gift card order ID!'),
            'gift_card_order_id.exists'=> __('Invalid gift card order ID!'),
            'message.required'=> __('Type a text!'),
            'message.string'=> __('Message must be string!'),
            'file.mimes'=>__('Only ').implode(',', $extensions). __(' file type is accepted!')
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
