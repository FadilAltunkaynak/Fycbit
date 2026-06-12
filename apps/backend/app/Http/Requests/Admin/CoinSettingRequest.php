<?php

namespace App\Http\Requests\Admin;

use App\Enums\NetworkBase;
use App\Facades\ResponseFacade;
use App\Model\Coin;
use App\Model\Network;
use Illuminate\Foundation\Http\FormRequest;

class CoinSettingRequest extends FormRequest
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
            'coin_id' => 'required',
            'coin_network_id' => 'required',
        ];

        $n_id = decryptId($this?->coin_network_id ?? 0);
        $network_id = gettype($n_id) == 'array' ? 0 : $n_id;
        if($network_id == 0) ResponseFacade::failed(__('Network not found'))->throw();

        $network = Network::find($network_id);
        if (!$network)
            return $rules;

        switch ($network->base_type) {
            case NetworkBase::BITGO_API->value:
                $rules += [
                    'bitgo_wallet_id' => 'required|max:255',
                    'bitgo_wallet' => 'required|max:255',
                    'chain' => 'required|integer'
                ];
                break;

            case NetworkBase::BITCOIN_API->value:
                $rules += [
                    'coin_api_user' => 'required|max:255',
                    'coin_api_pass' => 'required|max:255',
                    'coin_api_host' => 'required|max:255',
                    'coin_api_port' => 'required|max:255'
                ];
                break;
        }
        return $rules;
    }
}
