<?php

namespace Modules\IcoLaunchpad\Http\Controllers;

use App\Model\Coin;
use App\Jobs\MailSend;
use App\Model\Network;
use App\Model\CoinNetwork;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Services\CoinService;
use Illuminate\Routing\Controller;
use App\Http\Services\MyCommonService;
use App\Http\Services\AdminLangService;
use App\Http\Services\CoinSettingService;
use App\Http\Services\Evm\EvmWalletService;
use App\Model\CurrencyDepositPaymentMethod;
use Modules\IcoLaunchpad\Http\Requests\IcoRequest;
use Modules\IcoLaunchpad\Http\Services\IcoService;
use Modules\IcoLaunchpad\Http\Services\ERC20TokenApiService;


class ICOController extends Controller
{
    private $icoService;
    private $coinService;
    private $coinSettingService;
    private $erc20TokenApiService;
    private $languageService;
    public function __construct()
    {
        $this->icoService = new IcoService();
        $this->coinService = new CoinService();
        $this->coinSettingService = new CoinSettingService();
        $this->erc20TokenApiService = new ERC20TokenApiService();
        $this->languageService = new AdminLangService;
    }

    public function listICO()
    {
        $data['title'] = __('ICO List');

        $response = $this->icoService->getListICOToken();
        if ($response['success'] == true) {
            $data['ico_list'] = $response['data'];
        }
        return view('icolaunchpad::ico.ico-list', $data);
    }

    public function addNewICO()
    {
        $data['title'] = __('Add New ICO Token');
        $data['coins'] = Coin::where(['is_base' => STATUS_ACTIVE, 'trade_status' => STATUS_ACTIVE, 'status' => STATUS_ACTIVE])->get();
        $data['networks'] = Network::whereStatus(STATUS_ACTIVE)->get();

        return view('icolaunchpad::ico.addEdit-ICO', $data);
    }

    public function storeUpdateICO(IcoRequest $request)
    {
        $request->merge(['network' => $request->network_id]);
        $response = $this->icoService->storeUpdateICO($request);

        if ($response['success'] == true && !isset($request->id)) {
            return redirect()->route('addNewICOPhase', ['icoTokenId' => encrypt($response['data']->id)])->with(['success' => $response['message']]);
        } else {
            return back()->with(['success' => $response['message']])
                ->withInput($request->input());
        }
    }

    public function icoFeaturedStatusChange(Request $request)
    {
        $response = $this->icoService->icoFeaturedStatusChange($request->id);

        return response()->json(['message' => $response['message']]);
    }

    public function icoStatusChange(Request $request)
    {
        $response = $this->icoService->icoTokenStatusChange($request->id);

        return response()->json(['message' => $response['message']]);
    }

    public function editICO($id)
    {
        $data['title'] = __('Update ICO Token');
        $data['coins'] = Coin::where(['is_base' => STATUS_ACTIVE, 'trade_status' => STATUS_ACTIVE, 'status' => STATUS_ACTIVE])->get();
        $data['networks'] = Network::where(['status'=> STATUS_ACTIVE])->get();
        $get_ico_token = $this->icoService->findICOTokenByID(decryptId($id));
        if ($get_ico_token['success'] == true) {
            $data['item'] = $get_ico_token['data']['ico_phase_token'] ?? null;
        }
        return view('icolaunchpad::ico.addEdit-ICO', $data);
    }
    public function deleteICO($id)
    {
        $get_ico_response = $this->icoService->deleteICOTokenByID(decryptId($id));

        return back()->with(['success' => $get_ico_response['message']]);
    }

    public function acceptedICOToken(Request $request)
    {
        try {
            $response = $this->icoService->findICOTokenByID($request->id);
            if ($response['success'] == true && isset($response['data']['ico_phase_token']->user)) {
                $token_details = $response['data']['ico_phase_token'];

                $requestData = [
                    'contract_address' => $token_details->contract_address,
                    'id' => $token_details->network_id
                ];

                $checkContactAddressResponse = (new EvmWalletService())->checkContractAddress($requestData);

                if ($checkContactAddressResponse['success'] != true) {
                    $response = ['success' => false, 'message' => __('Your contract address is not valid!')];
                    return back()->with(['dismiss' => $response['message']]);
                } else {

                    $checkContactAddressResponseData = (object) $checkContactAddressResponse['data'];

                    $coin_data = [
                        'name' => $checkContactAddressResponseData->name,
                        'coin_type' => $checkContactAddressResponseData->symbol,
                        'network' => $token_details->network,
                        'coin_price' => 1,
                        'is_deposit' => 0,
                        'is_withdrawal' => 0,
                        'trade_status' => 0,
                        'is_wallet' => 0,
                        'is_buy' => 0,
                        'status' => 0,
                        'ico_id' => $token_details->id
                    ];

                    $coin_network_data = [
                        'uid'           => uniqid() . time(),
                        'network_id'    => $token_details->network_id,
                        'type'          => 1, // 1 for 'token'
                        'contract_address'=> $token_details->contract_address,
                        'status'        => STATUS_ACTIVE
                    ];

                    $coin_response = $this->coinService->saveCoinByICO($token_details->id, $coin_data);
                    if ($coin_response['success'] == false) {
                        return back()->with(['dismiss' => $coin_response['message']]);
                    } else if ($coin_response['success'] == true) {
                        $coin_details = $coin_response['data'];
                        $coin_setting_data = [
                            'coin_id' => $coin_details->id,
                            'contract_coin_name' => $token_details->base_coin,
                            'chain_link' => $token_details->chain_link,
                            'chain_id' => $token_details->chain_id,
                            'contract_address' => $token_details->contract_address,
                            'wallet_address' => $token_details->wallet_address,
                            'wallet_key' => $token_details->wallet_private_key, // encrypted
                            'contract_decimal' => $token_details->decimal,
                            'gas_limit' => $token_details->gas_limit,
                            'check_encrypt' => STATUS_SUCCESS,
                        ];

                        $coin_network_data["currency_id"] = $coin_details->id;
                        $CoinNetworkData = CoinNetwork::create($coin_network_data);

                        $coin_setting_response = $this->coinSettingService->storeERCorBEPCoinsettings($coin_setting_data);

                        if ($coin_setting_response['success'] == false) {
                            return back()->with(['dismiss' => $coin_setting_response['message']]);
                        }
                    }
                }

                $user = $token_details->user;
                $user_id = $user->id;
                $title = __('ICO Token is accepted');
                $message = $request->message;
                $mycommonService = new MyCommonService;
                $mycommonService->sendNotificationToUserUsingSocket($user_id, $title, $message);

                $data['mailTemplate'] = 'email.ico_form_accept_reject';
                $data['name'] = $user->first_name . ' ' . $user->last_name;
                $data['subject'] = $title;
                $data['details'] = $message;
                $data['to'] = $user->email;

                dispatch(new MailSend($data));

                $token_details->approved_status = STATUS_ACCEPTED;
                $token_details->approved_id = auth()->user()->id;
                $token_details->save();

                return back()->with(['success' => __('Submitted ICO Token accepted successfully!')]);
            }
            return back()->with(['dismiss' => __('User not found!')]);
        } catch (\Exception $e) {
            storeException("accpetedSubmittedFormICO", $e->getMessage());
        }
        return back()->with(['dismiss' => __('Something went wrong!')]);
    }

    public function modificationICOToken(Request $request)
    {
        try {
            $response = $this->icoService->findICOTokenByID($request->id);
            if ($response['success'] == true && isset($response['data']['ico_phase_token']->user)) {
                $tokenDetails = $response['data']['ico_phase_token'];
                $user = $tokenDetails->user;
                $user_id = $user->id;
                $title = __('ICO Token need some modification');
                $message = $request->message;
                $mycommonService = new MyCommonService;
                $mycommonService->sendNotificationToUserUsingSocket($user_id, $title, $message);

                $data['mailTemplate'] = 'email.ico_form_accept_reject';
                $data['name'] = $user->first_name . ' ' . $user->last_name;
                $data['subject'] = $title;
                $data['details'] = $message;
                $data['to'] = $user->email;

                dispatch(new MailSend($data));

                $tokenDetails->approved_status = STATUS_MODIFICATION;
                $tokenDetails->approved_id = auth()->user()->id;
                $tokenDetails->save();

                return back()->with(['success' => __('Submitted ICO Token modification email send successfully!')]);
            }
            return back()->with(['dismiss' => __('User not found!')]);
        } catch (\Exception $e) {
            storeException("accpetedSubmittedFormICO", $e->getMessage());
        }
        return back()->with(['dismiss' => __('Something went wrong!')]);
    }

    public function rejectedICOToken(Request $request)
    {
        try {
            $response = $this->icoService->findICOTokenByID($request->id);
            if ($response['success'] == true && isset($response['data']['ico_phase_token']->user)) {
                $icoToken = $response['data']['ico_phase_token'];
                $user = $icoToken->user;
                $user_id = $user->id;
                $title = __('ICO Token is rejected');
                $message = $request->message;
                $mycommonService = new MyCommonService;
                $mycommonService->sendNotificationToUserUsingSocket($user_id, $title, $message);

                $data['mailTemplate'] = 'email.ico_form_accept_reject';
                $data['name'] = $user->first_name . ' ' . $user->last_name;
                $data['subject'] = $title;
                $data['details'] = $message;
                $data['to'] = $user->email;

                dispatch(new MailSend($data));

                $icoToken->approved_status = STATUS_REJECTED;
                $icoToken->approved_id = auth()->user()->id;
                $icoToken->save();

                return back()->with(['success' => __('Submitted ICO Token rejected successfully!')]);
            }
            return back()->with(['dismiss' => __('User not found!')]);
        } catch (\Exception $e) {
            storeException("accpetedSubmittedFormICO", $e->getMessage());
        }
        return back()->with(['dismiss' => __('Something went wrong!')]);
    }

    public function getAddressDettailsApi(Request $request)
    {
        $requestData = [
            'contract_address' => $request->contract_address ?? "",
            'id' => $request->network ?? 0
        ];

        $response = (new EvmWalletService())->checkContractAddress($requestData);

        return response()->json($response);
    }

    public function paymentMethodList()
    {
        $data['title'] = __("Payment Method List");
        $data['items'] = CurrencyDepositPaymentMethod::whereType('ico_token')->get();
        return view('icolaunchpad::payment-method.list', $data);
    }

    public function paymentMethodAdd()
    {
        $data['title'] = __("Payment Method Add");
        $data['payment_methods'] = htmlPaymentMethod();
        return view('icolaunchpad::payment-method.addEdit', $data);
    }
    public function paymentMethodAddEdit($id)
    {
        $data['title'] = __("Payment Method Edit");
        $data['payment_methods'] = htmlPaymentMethod();
        $data['item'] = CurrencyDepositPaymentMethod::find($id);
        return view('icolaunchpad::payment-method.addEdit', $data);
    }

    public function paymentMethodAddProccess(Request $request)
    {
        try {
            $request->merge(['type' => 'ico_token']);
            if ((!isset($request->title)) && empty($request->title))
                return redirect()->back()->with('dismiss', __("Title is required"));
            if (!isset($request->payment_method_id))
                return redirect()->back()->with('dismiss', __("Select a payment method"));
            else
                $request->merge(['payment_method' => $request->payment_method_id]);
            if (isset($request->status)) $request->merge(['status' => STATUS_ACTIVE]);

            $response = responseData(true, __('Payment method created successfully'));
            $find = ['id' => 0];
            if (isset($request->id)) {
                $response['message'] = __('Payment method updated successfully');
                $find['id'] = $request->id;
            }
            $save = CurrencyDepositPaymentMethod::updateOrCreate($find, $request->except(['_token']));
            if (!$save) {
                if (isset($request->id))
                    return redirect()->route('IcoPaymentMethod')->with('dismiss', __("Payment method updated failed"));
                return redirect()->back()->with('dismiss', __("Payment method create failed"));
            }
            if ($response['success'])
                return redirect()->route('IcoPaymentMethod')->with('success', $response['message']);
            return redirect()->back()->with('dismiss', $response['message']);
        } catch (\Exception $e) {
            storeException('paymentMethodAddProccess:', $e->getMessage());
            return redirect()->back()->with('dismiss', __("Something went wrong"));
        }
    }

    public function paymentMethodAddDelete($id)
    {
        try {
            $delete = CurrencyDepositPaymentMethod::find($id)->first()->delete();
            if ($delete)
                return redirect()->back()->with('dismiss', __("Payment method deleted successfully"));
            return redirect()->back()->with('dismiss', __("Payment method delete failed"));
        } catch (\Exception $e) {
            storeException('paymentMethodAddDelete:', $e->getMessage());
            return redirect()->back()->with('dismiss', __("Something went wrong"));
        }
    }

    public function paymentMethodStatus(Request $request)
    {
        try {
            $status = CurrencyDepositPaymentMethod::find($request->id);
            if ($status) {
                $status->status = !$status;
                $status->save();
                return response()->json(['success' => true, 'message' => __("Success")]);
            }
            return response()->json(['success' => false, 'message' => __("Failed")]);
        } catch (\Exception $e) {
            storeException('paymentMethodAddDelete:', $e->getMessage());
            return response()->json(['success' => false, 'message' => __("Something went wrong")]);
        }
    }

    public function translationListICO($id)
    {
        $data = [];
        try {
            $data['title'] = __("Update Languages For ICO");

            $language_response = $this->languageService->activeLanguageList();
            if($language_response['success'])
            {
                $data['language_list'] = $language_response['data'];
            }

            $get_ico_token = $this->icoService->findICOTokenByID(decryptId($id));
            if ($get_ico_token['success']) {
                $data['token_details'] = $get_ico_token['data']['ico_phase_token'];
            }

        } catch (\Exception $e) {
            storeException('CategoryPage',$e->getMessage());
        }
        return view('icolaunchpad::ico.token-translation-list',$data);

    }

    public function tokenTranslateUpdatePage($id, $lang_key)
    {
        $data = [];
        try {
            $data['title'] = __("Language Update Blog");
            $get_ico_token = $this->icoService->findICOTokenByID(decryptId($id));

            $language_details_response = $this->languageService->languageDetailsByKey($lang_key);

            if($get_ico_token['success'] && $language_details_response['success']) {

                $token_details = $get_ico_token['data']['ico_phase_token'];
                $data['token_details'] = $token_details;
                $data['language_details'] = $language_details_response['data'];

                $token_translation_response = $this->icoService->getTokenDetailsTranslationByLangKey($token_details->id, $data['language_details']->key);
                if($token_translation_response['success'])
                {
                    $data['token_translation'] = $token_translation_response['data'] ;
                }

                return view('icolaunchpad::ico.token-translation-page',$data);
            }else{
                return back()->with(['success' => 'Invalid Request']);
            }

        } catch (\Exception $e) {
            storeException('tokenTranslateUpdatePage',$e->getMessage());
        }
        return back()->with(['success' => 'Invalid Request']);

    }

    public function updateLanguageTextToken(Request $request)
    {
        $response = $this->icoService->updateLanguageFortoken($request);
        if($response['success'])
        {
            return back()->with(['success' => $response['message']]);
        }else{
            return back()->with(['dismiss' => $response['message']]);
        }

    }
}
