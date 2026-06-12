<?php

namespace App\Http\Requests\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class CreateNetworkRequest extends FormRequest
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
        $ruls = [
            "name" => "required|string",
            "slug" => "required_without:id|string",
            "block_confirmation" => "required|numeric|integer|gte:1",
            "rpc_url" => "required",
            "explorer_url" => "required",
            //"logo" => "required_without:id|image|mimes:png,jpg,jpeg,gif,svg,webp",
        ];
        if(!empty($this->slug)){
            $ruls["slug"] = 'required|unique:networks';
        }
        return $ruls;
    }

    public function messages()
    {
        return [
            "name.required" => __("Network name is required"),
            "name.string" => __("Network name must be a string"),
            
            "slug.required_without" => __("Network slug is required"),
            "slug.required" => __("Network slug is required"),
            "slug.string" => __("Network slug name must be a string"),
            "slug.unique" => __("Network already exsist"),
            
            "block_confirmation.required" => __("Block Confirmation is required"),
            "block_confirmation.numeric" => __("Block Confirmation must be a number"),
            "block_confirmation.integer" => __("Block Confirmation must be a integer"),
            "block_confirmation.gte" => __("Block Confirmation must greater than or equal to zero"),

            "rpc_url.required" => __("RPC url is required"),
            "explorer_url.required" => __("Explorer url is required"),

            "logo.required_without" => __("Network logo is required"),
            "logo.image" => __("Network logo must be image"),
            "logo.mimes" => __("Supported Network file is (png,jpg,jpeg,gif,svg,webp)"),
        ];
    } 
}
