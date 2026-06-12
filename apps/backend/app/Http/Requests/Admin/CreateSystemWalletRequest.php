<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class CreateSystemWalletRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'creation_type' => 'required_without:uid|in:0,1',
            'network_id' => 'required_without:uid|exists:networks,id',
            'address' => [ Rule::requiredIf(!isset($this->uid) && isset($this->creation_type) && $this->creation_type == 1) ],
            'private_key' => [ Rule::requiredIf(!isset($this->uid) && isset($this->creation_type) && $this->creation_type == 1) ],
        ];
    }
    
    public function messages()
    {
        return [
            'network_id.required_without' => __("Network is required"),
            'network_id.exists' => __("Network not found"),

            'address.required' => __("Wallet Address is required"),
            'private_key.required' => __("Wallet private key is required"),
            
            'creation_type.required_without' => __("Creation type is required"),
            'creation_type.in' => __("Creation type is invalid"),
        ];
    }
}
