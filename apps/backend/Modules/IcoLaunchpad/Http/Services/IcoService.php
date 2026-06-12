<?php

namespace Modules\IcoLaunchpad\Http\Services;

use App\Http\Services\Evm\EvmWalletService;
use App\Model\Coin;
use App\Model\Network;
use App\Model\CoinNetwork;
use App\Http\Services\CoinService;
use App\Http\Services\AdminLangService;
use App\Http\Services\CoinSettingService;
use App\Model\SupportedNetwork;
use Modules\IcoLaunchpad\Entities\IcoToken;
use Modules\IcoLaunchpad\Entities\SubmitFormLists;
use Modules\IcoLaunchpad\Http\Repositories\IcoRepository;
use Modules\IcoLaunchpad\Http\Services\ERC20TokenApiService;

class IcoService
{

    private $coinService;
    private $icoRepository;
    private $erc20TokenApiService;
    private $coinSettingService;
    private $languageService;
    public function __construct()
    {
        $this->coinService = new CoinService();
        $this->icoRepository = new IcoRepository();
        $this->erc20TokenApiService = new ERC20TokenApiService();
        $this->coinSettingService = new CoinSettingService();
        $this->languageService = new AdminLangService;
    }
    public function getListICOToken($per_page = null)
    {
        try {
            $data = $this->icoRepository->getListICOToken($per_page);
            $response = ['success' => true, 'message' => __('ICO List!'), 'data' => $data];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("getListICO", $e->getMessage());
        }
        return $response;
    }

    public function getActiveICOList($per_page = 200, $extra = null)
    {
        try {
            $data = $this->icoRepository->getActiveICOList($per_page, $extra);
            $response = ['success' => true, 'message' => __('Active ICO Token List!'), 'data' => $data];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("getActiveICOList", $e->getMessage());
        }
        return $response;
    }

    public function getUserICOList($per_page = null, $search = null)
    {
        try {
            $data = $this->icoRepository->getUserICOList($per_page, $search);
            $response = ['success' => true, 'message' => __('User ICO List!'), 'data' => $data];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("getUserICOList", $e->getMessage());
        }
        return $response;
    }

    public function storeUpdateICO($request)
    {
        try {
            $user = auth()->user() ?? auth()->guard('api')->user();

            if(! $network = Network::find($request->network)) {
                return responseData(false, __("Seleted network not found!"));
            }

            if (in_array($network->base_type, [COIN_PAYMENT , BITGO_API, BITCOIN_API])) {
                return responseData(false, __("Invalid network to create ico"));
            }

            $selectedNet = SupportedNetwork::where(['slug' => $network->slug])->first();
            if ($user->role != USER_ROLE_ADMIN) {
                $submitted_form_details = SubmitFormLists::find($request->form_id);

                if (isset($submitted_form_details)) {
                    if ($submitted_form_details->status != STATUS_ACCEPTED) {
                        $response = ['success' => false, 'message' => __('You can submit a ICO Token after your form id is accepted')];
                        return $response;
                    }
                    if (empty($request->id)) {
                        $check_token = IcoToken::where('form_id', $request->form_id)->whereIn('approved_status', [STATUS_PENDING, STATUS_ACCEPTED, STATUS_MODIFICATION])->get();

                        if (count($check_token) > 0) {
                            $response = ['success' => false, 'message' => __('You have already a ICO Token, you can not create new ICO Token by this form!')];
                            return $response;
                        }
                    }
                } else {
                    $response = ['success' => false, 'message' => __('Submitted form Id is not found!')];
                    return $response;
                }
            }

            $requestData = [
                'contract_address' => $request->contract_address,
                'id' => $request->network
            ];

            $checkContactAddressResponse = (new EvmWalletService())->checkContractAddress($requestData);

            if ($checkContactAddressResponse['success'] != true) {
                $response = ['success' => false, 'message' => __('Your contract address is not valid!')];
                return $response;
            } else {
                $checkContactAddressResponseData = (object) $checkContactAddressResponse['data'];
            }

            $data = [
                'form_id' => $request->form_id,
                'base_coin' => $selectedNet->native_currency,
                'coin_type' => $checkContactAddressResponseData->symbol,
                'token_name' => $checkContactAddressResponseData->name,
                'network' => $request->network,
                'network_id' => $request->network,
                'wallet_address' => $request->wallet_address,
                'contract_address' => $request->contract_address,
                // 'wallet_private_key' => encrypt($request->wallet_private_key),
                'chain_id' => $checkContactAddressResponseData->chain_id,
                'chain_link' => $request->chain_link,
                'decimal' => $checkContactAddressResponseData->token_decimal,
                'gas_limit' => $request->gas_limit,
                'user_id' => $user->id,
                'status' => ($user->role == USER_ROLE_ADMIN) ? STATUS_ACCEPTED : STATUS_PENDING,
                'approved_id' => ($user->role == USER_ROLE_ADMIN) ? $user->id : null,
                'approved_status' => ($user->role == USER_ROLE_ADMIN) ? STATUS_ACCEPTED : STATUS_PENDING,
                'website_link' => $request->website_link,
                'details_rule' => $request->details_rule,
            ];

            if(isset($request->wallet_private_key))
                $data['wallet_private_key'] = custom_encrypt($request->wallet_private_key);

            $coin_data = [
                'name' => $checkContactAddressResponseData->name,
                'coin_type' => $checkContactAddressResponseData->symbol,
                'network' => $request->network,
                'coin_price' => 1,
                'is_deposit' => 0,
                'is_withdrawal' => 0,
                'trade_status' => 0,
                'is_wallet' => 0,
                'is_buy' => 0,
                'status' => 0,
            ];

            $coin_network_data = [
                'uid'           => uniqid() . time(),
                'network_id'    => $network->id,
                'type'          => 1, // 1 for 'token'
                'contract_address'=> $request->contract_address,
                'status'        => STATUS_ACTIVE
            ];

            $id = null;
            if (isset($request->id)) {
                $id = $request->id;
                $token_data = IcoToken::find($id);
                $old_image = $token_data->image;
                if (!empty($request->image)) {
                    $imageName = uploadAnyFile($request->image, FILE_ICO_STORAGE_PATH, $old_image);
                    $data['image_name'] = $imageName;
                    $data['image_path'] = asset(FILE_ICO_VIEW_PATH . $imageName);
                }

                if ($user->role != USER_ROLE_ADMIN) {

                    if( // update ico token when status modification or pending
                        $token_data->approved_status == APPROVE_STATUS_MODIFICATION ||
                        $token_data->approved_status == APPROVE_STATUS_PENDING
                    ){
                        if($token_data->approved_status == APPROVE_STATUS_MODIFICATION){
                            $data['approved_status'] = APPROVE_STATUS_PENDING;
                        }

                        return $this->icoRepository->storeUpdateICO($id, $data);
                    }

                    $response = $this->icoRepository->storeUpdateICORequest($id, $data);
                    if ($response['success'] == true) {
                        $coin_data['ico_id'] = $id;
                        $coin_response = $this->icoRepository->saveCoinByICORequest($id, $coin_data);
                        if ($coin_response['success'] == true) {
                            $coin_details = $coin_response['data'];
                            $coin_setting_data = [
                                'coin_id' => $coin_details->id,
                                'contract_coin_name' => $request->base_coin,
                                'chain_link' => $request->chain_link,
                                'chain_id' => $request->chain_id,
                                'contract_address' => $request->contract_address,
                                'wallet_address' => $request->wallet_address,
                                'wallet_key' => custom_encrypt($request->wallet_private_key),
                                'contract_decimal' => $request->decimal,
                                'gas_limit' => $request->gas_limit,
                                'check_encrypt' => STATUS_SUCCESS,
                            ];
                            $coin_setting_response = $this->coinSettingService->storeERCorBEPCoinsettings($coin_setting_data);

                            if ($coin_setting_response['success'] == false) {
                                return $coin_setting_response;
                            }
                        }
                    }
                } else {
                    $check_coin_type = Coin::where('ico_id','<>', $id)->where('coin_type', $coin_data['coin_type'])->get();
                    if (isset($check_coin_type[0])) {
                        $check_coin_type_response = ['success' => false, 'message' => __('This coin already exists, Please try with another coin')];
                        return $check_coin_type_response;
                    }

                    $response = $this->icoRepository->storeUpdateICO($id, $data);
                    $coin_data['ico_id'] = $token_data->id;
                    $coin_response = $this->coinService->saveCoinByICO($token_data->id, $coin_data);
                    
                    if ($coin_response['success'] == false) {
                        return $coin_response;
                    }else if ($coin_response['success'] == true) {
                        $coin_details = $coin_response['data'];
                        $coin_setting_data = [
                            'coin_id' => $coin_details->id,
                            'contract_coin_name' => $request->base_coin,
                            'chain_link' => $request->chain_link,
                            'chain_id' => $request->chain_id,
                            'contract_address' => $request->contract_address,
                            'wallet_address' => $request->wallet_address,
                            // 'wallet_key' => encrypt($request->wallet_private_key),
                            'contract_decimal' => $request->decimal,
                            'gas_limit' => $request->gas_limit,
                            'check_encrypt' => STATUS_SUCCESS,
                        ];

                        if(isset($request->wallet_private_key))
                            $coin_setting_data['wallet_key'] = custom_encrypt($request->wallet_private_key);

                        $coin_setting_response = $this->coinSettingService->storeERCorBEPCoinsettings($coin_setting_data);

                        if ($coin_setting_response['success'] == false) {
                            return $coin_setting_response;
                        }
                    }
                }
            } else {
                $old_image = null;
                if (!empty($request->image)) {
                    $imageName = uploadAnyFile($request->image, FILE_ICO_STORAGE_PATH, $old_image);
                    $data['image_name'] = $imageName;
                    $data['image_path'] = asset(FILE_ICO_VIEW_PATH . $imageName);
                }
                if ($user->role == USER_ROLE_ADMIN) {
                    $check_coin_type = Coin::where('coin_type', $coin_data['coin_type'])->get();
                    if (isset($check_coin_type[0])) {
                        $check_coin_type_response = ['success' => false, 'message' => __('This coin already exists, Please try with another coin')];
                        return $check_coin_type_response;
                    }
                    $response = $this->icoRepository->storeUpdateICO($id, $data);
                    $token_data = $response['data'];
                    $coin_data['ico_id'] = $token_data->id;
                    $coin_response = $this->coinService->saveCoinByICO($token_data->id, $coin_data);

                    if ($coin_response['success'] == false) {
                        return $coin_response;
                    }else if ($coin_response['success'] == true) {
                        $coin_details = $coin_response['data'];
                        $coin_setting_data = [
                            'coin_id' => $coin_details->id,
                            'contract_coin_name' => $request->base_coin,
                            'chain_link' => $request->chain_link,
                            'chain_id' => $request->chain_id,
                            'contract_address' => $request->contract_address,
                            'wallet_address' => $request->wallet_address,
                            // 'wallet_key' => encrypt($request->wallet_private_key),
                            'contract_decimal' => $request->decimal,
                            'gas_limit' => $request->gas_limit,
                            'check_encrypt' => STATUS_SUCCESS,
                        ];
                        $coin_network_data["currency_id"] = $coin_details->id;
                        $CoinNetworkData = CoinNetwork::create($coin_network_data);
                        
                        if(isset($request->wallet_private_key))
                            $coin_setting_data['wallet_key'] = custom_encrypt($request->wallet_private_key);

                        $coin_setting_response = $this->coinSettingService->storeERCorBEPCoinsettings($coin_setting_data);

                        if ($coin_setting_response['success'] == false) {
                            return $coin_setting_response;
                        }
                    }
                } else {
                    $response = $this->icoRepository->storeUpdateICO($id, $data);
                }
            }
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("storeUpdateICO", $e->getMessage());
        }
        return $response;
    }

    public function icoFeaturedStatusChange($id)
    {
        try {
            $response = $this->icoRepository->icoFeaturedStatusChange($id);
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("icoFeaturedStatusChange", $e->getMessage());
        }
        return $response;
    }

    public function icoTokenStatusChange($id)
    {
        try {
            $response = $this->icoRepository->icoTokenStatusChange($id);
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("icoTokenStatusChange", $e->getMessage());
        }
        return $response;
    }

    public function findICOTokenByID($id, $extra = null)
    {
        try {
            $response = $this->icoRepository->findICOTokenByID($id, $extra);
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("findICOByID", $e->getMessage());
        }
        return $response;
    }

    public function deleteICOTokenByID($id)
    {
        try {
            $response = $this->icoRepository->deleteICOTokenByID($id);
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("deleteICOByID", $e->getMessage());
        }
        return $response;
    }

    public function buyICOToken($request)
    {
        try {
            $ico_token_details = $this->icoRepository->findICOTokenByID($request->token_id);

            if ($ico_token_details['success'] == true) {
                $tokenDetails = $ico_token_details['data']['ico_phase_token'] ?? null;
                $get_ico_token = $tokenDetails?->latestICOPhaseDetails;
                return $get_ico_token;

                $rest_token_amount = $get_ico_token->total_token_supply - $get_ico_token->total_sell_token_supply;

                if ($request->amount <= $rest_token_amount) {
                    $data = [
                        'token_id' => $get_ico_token->id,
                        'user_id' => auth()->user()->id,
                        'amount' => $request->amount,
                        'buy_price' => $get_ico_token->coin_price,
                        'payment_method' => $request->payment_method,
                    ];

                    $response = $this->icoRepository->buyICOToken($data);
                } else {
                    $response = ['success' => false, 'message' => __('There are no sufficient token to buy!')];
                }
            } else {
                $response = ['success' => false, 'message' => __('Token is not found!')];
            }
            // if($get_ico_response['su'])
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("buyICOToken", $e->getMessage());
        }
        return $response;
    }

    public function updateLanguageFortoken($request)
    {
        try {
            $ico_token_details = $this->icoRepository->findICOTokenByID($request->ico_token_id);
            $language_details_response = $this->languageService->languageDetailsByKey($request->lang_key);
            if ($ico_token_details['success'] && $language_details_response['success']) {
                $response = $this->icoRepository->updateICOTokenLanguage($request);
            } else {
                $response = ['success' => false, 'message' => __('Invalid Request!')];
            }
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("updateLanguageFortoken", $e->getMessage());
        }
        return $response;
    }

    public function getTokenDetailsTranslationByLangKey($token_id, $lang_key)
    {
        $response = $this->icoRepository->getTokenDetailsTranslationByLangKey($token_id, $lang_key);
        return $response;
    }
}
