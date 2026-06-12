<?php

namespace Modules\IcoLaunchpad\Http\Services;

use App\User;
use App\Model\Bank;
use App\Model\Coin;
use App\Model\Wallet;
use App\Jobs\MailSend;
use App\Model\Network;
use App\Model\AdminBank;
use App\Model\CoinNetwork;
use App\Model\CurrencyList;
use Illuminate\Support\Str;
use App\Model\SupportedNetwork;
use PhpParser\Node\Stmt\TryCatch;
use App\Http\Services\MailService;
use Illuminate\Support\Facades\DB;
use App\Model\WalletAddressHistory;
use App\Http\Services\ERC20TokenApi;
use App\Http\Services\MyCommonService;
use App\Services\BankService\BankService;
use Modules\IcoLaunchpad\Jobs\BuyTokenJob;
use App\Http\Services\Evm\EvmWalletService;
use App\Model\CurrencyDepositPaymentMethod;
use Modules\IcoLaunchpad\Entities\IcoToken;
use App\Http\Services\CurrencyDepositService;
use Modules\IcoLaunchpad\Entities\IcoPhaseInfo;
use Modules\IcoLaunchpad\Entities\TokenBuyEarn;
use Modules\IcoLaunchpad\Jobs\TokenBuyAcceptJob;
use Modules\IcoLaunchpad\Entities\TokenBuyHistory;
use Symfony\Component\HttpFoundation\ParameterBag;


class IcoTokenBuyService
{

    private $admin_approved;
    private $myCommonService;

    public function __construct()
    {
        $this->myCommonService = new MyCommonService();
        $approved = settings('icoTokenBuy_admin_approved');
        $this->admin_approved = ($approved == STATUS_ACTIVE);
    }

    private function create($data)
    {
        try {

            return TokenBuyHistory::create($data);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function sendEmailAndNotification($title, $message, $user_id = false)
    {
        $user = null;
        if (auth()->check())
            $user = auth()->user() ?? auth()->guard('api')->user();
        if ($user_id)
            $user = User::find($user_id);

        $this->myCommonService->sendNotificationToUserUsingSocket(
            $user->id,
            $title,
            $message
        );
        $emailData = [
            'to' => $user->email,
            'name' => $user->first_name . ' ' . $user->last_name,
            'subject' => $title,
            'email_header' => $title,
            'email_message' => $message,
            'mailTemplate' => 'email.genericemail'
        ];
        dispatch(new MailSend($emailData))->onQueue('send-mail');
    }

    public function addBalance($wallet_id, $address, $buyToken)
    {
        try {
            if ($buyToken->status == STATUS_ACTIVE) {
                storeException('addBalance', 'already approved');
            } else {
                $icoOwnerReceiveWallet = null;
                if (!$wallet = Wallet::find($wallet_id)) {
                    storeException('addBalance ico', 'wallet not found');
                    return false;
                }
                if (!$phase = IcoPhaseInfo::find($buyToken->phase_id)) {
                    storeException('addBalance ico', 'Ico phase not found');
                    return false;
                }

                if ($buyToken->payment_method == CRYPTO) {
                    if (!$icoOwnerReceiveCoin = Coin::where('coin_type', $buyToken->pay_currency)->first()) {
                        storeException('addBalance ico', 'Ico owner receive coin not found');
                        return false;
                    }

                    if (!$icoOwnerReceiveWallet = Wallet::where(["user_id" => $phase->user_id, "coin_type" => $buyToken->pay_currency])->first()) {
                        storeException('addBalance ico', 'Ico owner receive wallet not found');
                        return false;
                    }

                }

                $coin = Coin::join('coin_settings', 'coin_settings.coin_id', '=', 'coins.id')
                    ->where(['coins.id' => $buyToken->coin_id])
                    ->select('coins.*', 'coin_settings.*')
                    ->first();

                if(! $walletAddress = WalletAddressHistory::where(['coin_id' => $coin->coin_id,'wallet_id' => $wallet->id,'address' => $address])->first()){
                    storeException('Wallet address not found', 'transaction failed');
                    return false;
                }
                
                if(! $network = Network::find($walletAddress->network_id)){
                    storeException('Network not found', 'transaction failed');
                    return false;
                }
                
                if(! $coinNetwork = CoinNetwork::where(['currency_id' => $coin->coin_id, 'network_id' => $network->id])->first()){
                    storeException('Coin Network not found', ['currency_id' => $coin->coin_id, 'network_id' => $network->id, 'network_idd' => $walletAddress->network_id]);
                    return false;
                }
                
                if(! $supportedNetwork = SupportedNetwork::where('slug', $network->slug)->first()){
                    storeException('Supported network not found', 'transaction failed');
                    return false;
                }

                if ($coin) {
                    $coinApi = new EvmWalletService();

                    $requestData = [
                        "rpcUrl"           => $network->rpc_url ?? "",
                        "contract_address" => $coinNetwork->contract_address ?? "",
                        "coinType"         => $coin->coin_type,
                        "native_currency"  => $supportedNetwork->native_currency,
                        "coinDecimal"      => $coin->decimal,
                        "gasLimit"         => $supportedNetwork->gas_limit,
                        "from_address"     => $coin->wallet_address,
                        "to_address"       => $address,
                        "pk"               => custom_decrypt($coin->wallet_key),
                        "amount"           => (float)$buyToken->amount,
                    ];

                    $result = $coinApi->sendEvmToken($requestData);

                    if ($result['success'] ==  true) {
                        storeException('ssss', $result['data']);
                        $result['data'] = (object) $result['data'];
                        $data['transaction_id'] = $result['data']->transaction_id;
                        $data['used_gas'] = $result['data']->used_gas;
                        $buyToken->update(['status' => STATUS_ACTIVE, 'blockchain_tx' => $data['transaction_id'], 'used_gas' => $data['used_gas']]);
                        if ($buyToken->payment_method !== CRYPTO) {
                            $OwnerEarn = $this->addOwnerEarnings($buyToken);
                        } else {
                            $icoOwnerReceiveWallet->increment('balance', $buyToken->pay_amount);
                        }
                        $wallet->increment('balance', $buyToken->amount);
                        $subject = __('Token buy request');
                        $body = __('Your token buy success');
                        $this->sendEmailAndNotification($subject, $body, $wallet->user_id);

                        storeException('send token when buy token', 'transaction successful tx ->' . $data['transaction_id']);
                        return true;
                    } else {
                        storeException('send token when buy token', $result['message']);
                        return false;
                    }
                } else {
                    storeException('send token when buy token', 'coin not found');
                    return false;
                }
            }
        } catch (\Exception $e) {
            storeException('addBalance ex', $e->getMessage() . ' ' . $e->getLine());
            return false;
        }
        return false;
    }

    private function addOwnerEarnings($tokenBuy)
    {
        try {
            if ($tokenBuy->payment_method == CRYPTO)
                responseData(true, __("Success"));

            $phase = IcoPhaseInfo::findOrFail($tokenBuy->phase_id);
            $earns = TokenBuyEarn::firstOrNew(['user_id' => $phase->user_id]);
            $earns->save();
            if ($tokenBuy->pay_currency == 'USD') {
                $earns->increment('earn', $tokenBuy->pay_amount);
                $earns->increment('available', $tokenBuy->pay_amount);
            } else {
                $currency = 'USDT';
                $currency2 = 'USD';
                $earn = convert_currency($tokenBuy->pay_amount, $currency, $tokenBuy->buy_currency, $currency2);
                $earns->increment('earn', $earn);
                $earns->increment('available', $earn);
            }
            $earns->update();
        } catch (\Exception $e) {
            storeException('addOwnerEarnings ex', $e->getMessage());
            return responseData(false, __("Coin Owner earns history data save failed"));
        }
        storeException('addOwnerEarnings ex', __("Success"));
        return responseData(true, __("Success"));
    }

    private function makeData($phase, $data)
    {
        try {
            $data->buy_price = $phase->coin_price;
            $data->buy_currency = $phase->coin_currency;
            $sendData = [
                'phase_id' => $data->phase_id,
                'token_id' => $data->token_id,
                'payment_method' => $data->payment_method,
                'wallet_id' => $data->wallet_id,
                'coin_id' => $data->coin_id,
                'user_id' => $data->user_id,
                'amount' => $data->amount,
                'buy_price' => $phase->coin_price,
                'buy_currency' => $phase->coin_currency,
            ];
            if ($data->payment_method == CRYPTO) {
                $sendData['payer_wallet'] = $data->payer_wallet;
                $sendData['payer_coin'] = $data->payer_coin;
            }
            if ($data->payment_method == BANK_DEPOSIT) {
                $sendData['bank_slip'] = $data->bank_slip;
                $sendData['bank_id'] = $data->bank_id;
                $sendData['bank_ref'] = $data->bank_ref;
            }
            if (!empty($data->trx_id)) {
                $sendData['trx_id'] = $data->trx_id;
            }
            $response = $this->getPriceInfo($data);

            if ($response['success']) {
                $sendData['pay_amount'] = $response['data']['pay_amount'];
                $sendData['pay_currency'] = $response['data']['pay_currency'];
            }
            return $sendData;
        } catch (\Exception $e) {
            storeException('IcoTokenBuyService makeData', $e->getMessage());
            return [];
        }
    }

    public function tokenBuyJob($request)
    {
        try {
            $phase = $this->checkValidation($request);
            if ($phase['success']) {
                $wallet = $phase['data']['wallet'];
                $walletAddress = $phase['data']['address'];
                $phaseInfo = $phase['data']['phase'];
                if ($wallet) {
                    $request->wallet_id = $wallet->id;
                    $request->coin_id = $wallet->coin_id;
                    $payment = $this->paymentProcess($request, $phaseInfo);
                    if ($payment['success']) {
                        if ($request->payment_method == BANK_DEPOSIT)
                            $request->trx_id = uniqid() . date('') . time();

                        if ($request->payment_method == STRIPE)
                            $request->trx_id = $payment['data']['transaction_id'];

                        if ($request->payment_method == CRYPTO)
                            $request->payer_coin = $payment['data'];

                    } else {
                        $this->sendEmailAndNotification('ICO Token Buy Failed', $payment['message'], $request->user_id);
                        storeException('ico-tokenBuyJob failed:', $payment['message']);
                        return false;
                    }
                    $data = $this->makeData($phaseInfo, $request);
                    $data = (array) $data;

                    $phaseInfo->decrement('available_token_supply', $data['amount']);
                    $save = $this->create($data);
                    if ($save) {
                        if ($this->admin_approved && !($request->payment_method == BANK_DEPOSIT)) {
                            $this->addBalance($wallet->id, $walletAddress, $save);
                        }
                    }

                    $this->sendEmailAndNotification('ICO token buy request complete', __("Your buy token request is successfully placed"), $request->user_id);
                    storeException('ico-tokenBuyJob done:', json_encode($save));
                } else {
                    $this->sendEmailAndNotification('Wallet not found', __('Your wallet not found'), $request->user_id);
                    storeException('ico-tokenBuyJob:', __('Wallet not found'));
                }
            } else {
                $this->sendEmailAndNotification('Check validation failed', $phase['message'], $request->user_id);
                storeException('ico-tokenBuyJob:', $phase['message']);
            }
        } catch (\Exception $e) {
            $this->sendEmailAndNotification('ICO Token Buy Failed', __('ICO Token Buy Failed'), $request->user_id);
            storeException('ico-tokenBuyJob error:', $e->getMessage() . $e->getLine());
        }
    }

    public function tokenBuyJobNew($request)
    {
        try {
            $phase = $this->checkValidation($request);
            if ($phase['success']) {
                $wallet = $phase['data']['wallet'];
                $walletAddress = $phase['data']['address'];
                $phaseInfo = $phase['data']['phase'];
                if ($wallet) {
                    $request->wallet_id = $wallet->id;
                    $request->coin_id = $wallet->coin_id;

                    $data = $this->makeData($phaseInfo, $request);
                    $data = (array) $data;

                    $save = $this->create($data);

                    if ($save) {
                        $response_data['token_buy_history'] = $save;
                        $response_data['walletAddress'] = $walletAddress;
                        $response = ['success' => true, 'message' => __('ICO Token buy history is created successfully!'), 'data' => $response_data];
                    } else {
                        $response = ['success' => false, 'message' => __('ICO Token buy history is not created, try again!')];
                    }
                } else {
                    $response = ['success' => false, 'message' => __('Your wallet not found!')];
                    storeException('ico-tokenBuyJob:', __('Wallet not found'));
                }
            } else {
                $response = ['success' => false, 'message' => $phase['message']];
                storeException('ico-tokenBuyJob:', $phase['message']);
            }
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('ICO Token buy history is not created, try again!')];
            storeException('ico-tokenBuyJob error:', $e->getMessage() . $e->getLine());
        }
        return $response;
    }

    public function tokenBuyRequest($request)
    {
        $response = ['success' => true, 'message' => __('Request submitted successfully')];
        try {
            $checkValidation = $this->checkValidation($request);
            if ($checkValidation['success']) {
                $data = [];
                foreach ($request->request as $k => $r)
                    $data[$k] = $r;
                if (isset($request->bank_slep) && $request->hasFile('bank_slep')) {
                    $image = uploadimage($request->bank_slep, IMG_SLEEP_VIEW_PATH);
                    $data['bank_slip'] = $image;
                }

                BuyTokenJob::dispatch((object) $data)->onQueue('ico-buy-token');
            } else {
                return responseData(false, $checkValidation['message']);
            }
        } catch (\Exception $e) {
            storeException('tokenBuyRequest :', $e->getMessage());
            storeException('tokenBuyRequest :', $e->getTraceAsString());
            return ['success' => false, 'message' => __('Request submitted failed')];
        }
        return $response;
    }

    public function tokenBuyRequestNew($request)
    {
        $response = ['success' => true, 'message' => __('Request submitted successfully')];
        try {
            $checkValidation = $this->checkValidation($request);
            if ($checkValidation['success']) {
                $create_token_buy_response = $this->tokenBuyJobNew($request);
                if ($create_token_buy_response['success']) {
                    $token_buy_history = $create_token_buy_response['data']['token_buy_history'];
                    $walletAddress = $create_token_buy_response['data']['walletAddress'];
                    $phaseInfo = IcoPhaseInfo::findOrFail($token_buy_history->phase_id);
                    if ($request->payment_method == PAYSTACK) {
                        $paystact_response = $this->getPaystackPaymentURL($token_buy_history->id, $request->amount, $request->email, $walletAddress);
                        return $paystact_response;
                    } else {
                        $payment = $this->paymentProcess($request, $phaseInfo);
                        if ($payment['success']) {
                            $buy_history = TokenBuyHistory::find($token_buy_history->id);
                            if ($request->payment_method == BANK_DEPOSIT) {
                                $buy_history->trx_id = uniqid() . date('') . time();
                                if (isset($request->bank_slep) && $request->hasFile('bank_slep')) {
                                    $image = uploadimage($request->bank_slep, IMG_SLEEP_VIEW_PATH);
                                    $buy_history->bank_slip = $image;
                                }
                            }
                            if ($request->payment_method == STRIPE) {
                                $buy_history->trx_id = $payment['data']['transaction_id'];

                            }
                            if ($request->payment_method == CRYPTO) {
                                $buy_history->payer_coin = $payment['data'];
                            }
                            $buy_history->save();

                            $phaseInfo->decrement('available_token_supply', $request->amount);

                            if ($this->admin_approved && !($request->payment_method == BANK_DEPOSIT)) {
                                $this->addBalance($token_buy_history->wallet_id, $walletAddress, $token_buy_history);
                            }
                            $response = ['success' => true, 'message' => __('Request is submitted successfully')];
                        } else {
                            $response = ['success' => false, 'message' => $payment['message']];
                            return $response;
                        }
                    }
                } else {
                    $response = $create_token_buy_response;
                }
            } else {
                return responseData(false, $checkValidation['message']);
            }

        } catch (\Exception $e) {
            storeException('tokenBuyRequest :', $e->getMessage());
            storeException('tokenBuyRequest :', $e->getTraceAsString());
            return ['success' => false, 'message' => __('Request submitted failed')];
        }
        return $response;
    }

    private function createWallet($request)
    {
        $data['wallet'] = '';
        $data['address'] = '';
        try {
            //$phase = IcoPhaseInfo::findOrFail($request->phase_id);
            $token = IcoToken::findOrFail($request->token_id);
            $coin = Coin::whereCoinType($token->coin_type)->first();
            $wallet = Wallet::where(['coin_id' => $coin->id, 'user_id' => $request->user_id,])->first();

            $walletData = [
                'user_id' => $request->user_id,
                'name' => $token->token_name,
                'coin_id' => $coin->id,
                'coin_type' => $coin->coin_type,
                'status' => STATUS_ACTIVE
            ];
            if ($wallet) {
                $data['wallet'] = $wallet;
            } else {
                $data['wallet'] = Wallet::create($walletData);
            }

            if ($data['wallet']) {
                $walletData = Wallet::join('coins', 'coins.id', '=', 'wallets.coin_id')
                    ->where(['wallets.id' => $data['wallet']->id, 'wallets.user_id' => $data['wallet']->user_id])
                    ->select('wallets.*', 'coins.coin_icon', 'coins.network', 'coins.is_deposit')
                    ->first();
                $address = getWalletAddress($walletData, $token->network_id);

                $data['address'] = $address;
                if (empty($address)) {
                    storeException('tokenBuyCreateWallet:', 'no address found for this wallet');
                }
            }
        } catch (\Exception $e) {
            storeException('tokenBuyCreateWallet:', $e->getMessage());
        }
        return $data;
    }

    public function tokenBuyAcceptJob($data)
    {
        try {
            $address = WalletAddressHistory::whereWalletId($data->wallet_id)->first();
            if ($address) {
                if ($this->addBalance($data->wallet_id, $address->address, $data)) {
                    $subject = __('Token buy request accepted');
                    $body = __('Your buy token request has been accepted by admin');
                    $this->sendEmailAndNotification($subject, $body, $data->user_id);
                } else
                    storeException('tokenBuyAcceptJob', __('Failed to accept'));
            } else
                storeException('tokenBuyAcceptJob', __('Wallet not found'));
        } catch (\Exception $e) {
            storeException('tokenBuyAcceptJob ex', $e->getMessage());
        }
    }

    public function tokenBuyRequestAccept($id)
    {
        try {
            $data = TokenBuyHistory::findOrFail(decrypt($id));
            TokenBuyAcceptJob::dispatch($data)->onQueue('ico-buy-token');
        } catch (\Exception $e) {
            storeException("tokenBuyRequestAccept:", $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
        return responseData(true, __('Accept request place on queue successfully'));
    }

    public function tokenBuyRequestReject($id)
    {
        DB::beginTransaction();
        try {
            $data = TokenBuyHistory::findOrFail(decrypt($id));
            $data->status = STATUS_REJECTED;
            if ($data->payment_method == CRYPTO) {
                $wallet = Wallet::find($data->payer_wallet);
                $phase = IcoPhaseInfo::findOrFail($data->phase_id);
                $phase->increment('available_token_supply', $data->amount);
                $wallet->increment('balance', $data->payer_coin);
            }
            $data->save();
            $subject = __('Token buy request rejected');
            $body = __('Your buy token request has been rejected by admin');
            $this->sendEmailAndNotification($subject, $body);
        } catch (\Exception $e) {
            DB::rollBack();
            storeException("tokenBuyRequestReject:", $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
        Db::commit();
        return responseData(true, __('Request Rejected successfully'));
    }

    public function getTokenBuyPageData()
    {
        try {
            $bankService = new BankService();
            $user = getAuthUser();
            $data = [];
            $data['bank'] = $bankService->adminBankList()['data'] ?? null;
            $data['wallet'] = Wallet::whereUserId($user->id)->whereStatus(STATUS_ACTIVE)->get(['id', 'name', 'coin_type', 'balance']);
            $data['payment_methods'] = CurrencyDepositPaymentMethod::whereStatus(STATUS_ACTIVE)->whereType('ico_token')->get();
            $data['currency_list'] = CurrencyList::whereStatus(STATUS_ACTIVE)->orderBy('id', 'desc')->get();
            $data['ref'] = uniqid() . time() . '_' . $user->id;
            return responseData(true, __('Data get successfully'), $data);
        } catch (\Exception $e) {
            storeException('getTokenBuyPageData:', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    public function getTokenBuyHistory($request, $type)
    {
        try {
            $perPage = $request->per_page ?? 10;
            $user = getAuthUser();
            $query = TokenBuyHistory::join('ico_tokens', 'ico_tokens.id', '=', 'token_buy_histories.token_id')
                ->where('token_buy_histories.user_id', $user->id)
                ->when(isset($request->search), function ($query) use ($request) {
                    $query->where(function ($q) use ($request) {
                        $q->orWhere('amount', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('trx_id', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('pay_currency', 'LIKE', '%' . $request->search . '%');
                    });

                    if (Str::contains(strtolower($request['search']), 'paypal')) {
                        $query->orWhere('payment_method', PAYPAL);
                    } elseif (Str::contains(strtolower($request['search']), 'stripe')) {
                        $query->orWhere('payment_method', STRIPE);
                    } elseif (Str::contains(strtolower($request['search']), 'bank deposit')) {
                        $query->orWhere('payment_method', BANK_DEPOSIT);
                    } elseif (Str::contains(strtolower($request['search']), 'paystack')) {
                        $query->orWhere('payment_method', PAYSTACK);
                    }
                })
                ->orderBy('id', 'desc')
                ->select('token_buy_histories.*', 'ico_tokens.coin_type as token_name');

            if ($type || $type === '0')
                $query->where('token_buy_histories.status', $type);
            $data = $query->paginate($perPage);
            $data->map(function ($row) {
                $row->payment_method = htmlPaymentMethod($row->payment_method);
            });
            return responseData(true, __('Token buy history successfully'), $data);
        } catch (\Exception $e) {
            storeException('getTokenBuyHistory:', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    private function checkWalletBalance($price, $payer_wallet_id, $amount)
    {
        try {
            $wallet = Wallet::findOrFail($payer_wallet_id);
            $price = bcmul($amount, $price, 2);
            $coin = convert_currency($price, $wallet->coin_type, "USDT");
            if ($coin > $wallet->balance) {
                return responseData(false, __("Insufficient wallet balance"));
            }
            return responseData(true, __("Balance checked success"));
        } catch (\Exception $e) {
            return responseData(false, __("Something went wrong"));
        }
    }

    public function checkPhase($request)
    {
        try {
            $phase = IcoPhaseInfo::findOrFail($request->phase_id);
            if ($phase->status == STATUS_ACTIVE) {
                $start = date('Y-m-d H:i', strtotime($phase->start_date));
                $end = date('Y-m-d H:i', strtotime($phase->end_date));
                $current = date('Y-m-d H:i');
                $availableBuyToken = $phase->total_token_supply - checkSoldIcoToken($phase->id);
                if (($start < $current) && ($end > $current)) {
                    if (isset($request->amount) && $request->amount > $availableBuyToken) {
                        return responseData(false, __('Phase do not have enough balance'));
                    } elseif (
                        ($phase->maximum_purchase_price > 0) &&
                        !($phase->minimum_purchase_price <= $request->amount &&
                            $phase->maximum_purchase_price >= $request->amount)
                    ) {
                        return responseData(false, __('Your purchases amount must have to between minimum and maximum price!'));
                    } else {
                        if (isset($request->payment_method)) {
                            if ($request->payment_method == CRYPTO) {
                                if (isset($request->payer_wallet)) {
                                    $response = $this->checkWalletBalance($phase->coin_price, $request->payer_wallet, $request->amount);
                                    if ($response['success']) {
                                        return responseData(true, __("Phase checked successfully"), $phase);
                                    }
                                    return responseData(false, $response['message']);
                                }
                                return responseData(false, __("Wallet Id is required"));
                            }
                            return responseData(true, __("Phase checked successfully"), $phase);
                        }
                        return responseData(false, __("Payment method is required"));
                    }
                } else if ($start > $current) {
                    return responseData(false, __('Phase is not start yet, Please wait for start the phase!'));
                }
                return responseData(false, __('Phase date is expired'));
            }
            return responseData(false, __('Phase not activated'));
        } catch (\Exception $e) {
            storeException('checkPhase:', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    private function checkValidation($request)
    {
        $response = responseData(true, __('Validation success'));
        // check phase
        $phaseCheck = $this->checkPhase($request);
        if ($phaseCheck['success'] == false) {
            return responseData(false, $phaseCheck['message']);
        }
        $checkPaymentMethod = CurrencyDepositPaymentMethod::where(['payment_method' => $request->payment_method])
            ->where(['status' => STATUS_ACTIVE, 'type' => 'ico_token'])
            ->first();
        if (empty($checkPaymentMethod)) {
            return responseData(false, __("Invalid payment method"));
        }
        $data['phase'] = $phaseCheck['data'];
        // check wallet
        $wallet = $this->createWallet($request);
        if (!empty($wallet['wallet']) && !empty($wallet['address'])) {
            $data['wallet'] = $wallet['wallet'];
            $data['address'] = $wallet['address'];
            return responseData(true, __('Validation success'), $data);
        } else {
            return responseData(false, __("Wallet or address generate failed"));
        }
        return $response;
    }

    private function paymentProcess($data, $phase)
    {
        try {
            $response = responseData(false, __('No payment done'));
            $user = User::find($data->user_id);
            $price = $phase->coin_price;
            $service = new CurrencyDepositService();
            if ($data->payment_method == STRIPE) {
                $amountPrice = $data->amount * $price;
                $response = $service->depositWithStripe([
                    'amount' => $amountPrice,
                    'stripeToken' => $data->stripe_token,
                    'user_email' => $user->email
                ], $data->pay_currency);
                return $response;
            }
            if ($data->payment_method == CRYPTO) {
                $wallet = Wallet::where(['id' => $data->payer_wallet, 'user_id' => $user->id])->first();
                if ($wallet) {
                    $price = bcmul($data->amount, $price, 2);
                    $coin = convert_currency($price, $wallet->coin_type, $phase->coin_currency);
                    $wallet->decrement('balance', $coin);
                    return responseData(true, __('Crypto payment complete'), $coin);
                }
                storeException('paymentProcess failed', 'wallet not found');
                return responseData(false, __('Crypto payment not complete'));
            }
            if ($data->payment_method == PAYPAL) {
                if (isset($data->trx_id)) {
                    return responseData(true, __('Paypal payment complete'));
                }
                return responseData(false, __('Paypal payment not complete'));
            }
            if ($data->payment_method == BANK_DEPOSIT) {
                if ($data->hasFile('bank_slep')) {
                    return responseData(true, __('Bank payment complete'));
                }
                return responseData(false, __('Bank payment slip not found, so payment not complete'));
            }
            if ($data->payment_method == PAYSTACK) {
                if (isset($data->trx_id)) {
                    return responseData(true, __('Paystack payment complete'));
                }
                return responseData(false, __('Paystack payment not complete'));
            }
            if ($data->payment_method == SKRILL) {
                if (isset($data->trx_id)) {
                    return responseData(true, __('Skrill payment complete'));
                }
                return responseData(false, __('Skrill payment not complete'));
            }
            return $response;
        } catch (\Exception $e) {
            storeException('paymentProcess:', $e->getMessage());
            return responseData(false, __('failed'));
        }
    }

    public function getPriceInfo($request)
    {
        try {
            $user = User::find($request->user_id);
            $phase = IcoPhaseInfo::findOrFail($request->phase_id);
            $price = bcmul($request->amount, $phase->coin_price, 8);
            $currency = 'USDT';
            $currency2 = null;
            if ($request->payment_method == CRYPTO) {
                $wallet = Wallet::where(['id' => $request->payer_wallet, 'user_id' => $user->id])->first();
                $currency = $wallet->coin_type;
                $savedCurrency = $wallet->coin_type;
            } else {
                $currency2 = $request->pay_currency;
                $savedCurrency = $request->pay_currency;
            }

            $total_amount = convert_currency($price, $currency, $phase->coin_currency, $currency2);
            $data = [
                'token_price' => $phase->coin_price,
                'token_currency' => $phase->coin_currency,
                'token_amount' => $request->amount,
                'token_total_price' => $price,
                'pay_amount' => custom_number_format($total_amount),
                'pay_currency' => $savedCurrency
            ];
            return responseData(true, __("Price info get successfully"), $data);
        } catch (\Exception $e) {
            storeException('getPriceInfo', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    // get wallet list
    public function getUserTokenWalletList($request)
    {
        try {
            $user = getAuthUser();
            $wallet = Wallet::join('coins', 'coins.id', '=', 'wallets.coin_id')
                ->join('wallet_address_histories', 'wallet_address_histories.wallet_id', '=', 'wallets.id')
                ->join('ico_tokens', 'ico_tokens.id', '=', 'coins.ico_id')
                ->where(['wallets.user_id' => $user->id, 'wallets.type' => PERSONAL_WALLET])
                ->where('coins.ico_id', '<>', 0)
                ->when(isset($request->search), function ($query) use ($request) {
                    $query->where(function ($q) use ($request) {
                        $q->orWhere('wallet_address_histories.address', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('wallets.balance', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('coins.name', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('coins.coin_type', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('wallets.created_at', 'LIKE', '%' . $request->search . '%');
                    });
                })
                ->orderBy('wallets.id', 'ASC')
                ->select(
                    'wallets.*',
                    'wallet_address_histories.address',
                    'coins.coin_icon',
                    'coins.is_withdrawal',
                    'coins.is_deposit',
                    'coins.trade_status',
                    'ico_tokens.image_path'
                )
                ->paginate(20);
            return responseData(true, __("Wallet list"), $wallet);
        } catch (\Exception $e) {
            storeException('getPriceInfo', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    public function getPaystackPaymentURL($buy_history_id, $amount, $email, $walletAddress)
    {
        $url = "https://api.paystack.co/transaction/initialize";

        $secret_key = allsetting('PAYSTACK_SECRET');

        $callback_url = allsetting('exchange_url') . '/verify-paystack?buy_history_id=' . $buy_history_id . '&walletAddress=' . $walletAddress . '&api_type=ico';

        $currency_ZAR = CurrencyList::where('code', 'ZAR')->first();
        $currency_rate_ZAR = isset($currency_ZAR) ? $currency_ZAR->rate : 1;
        $converted_amount = $amount * $currency_rate_ZAR;

        $fields = [
            'email' => $email,
            'amount' => str_replace('.', '', number_format($converted_amount, 2, '.', '')),
            'callback_url' => $callback_url
        ];

        $fields_string = http_build_query($fields);


        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer " . $secret_key,
            "Cache-Control: no-cache",
        ));

        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //execute post
        $result = curl_exec($ch);

        $result_json_data = json_decode($result);
        $data = [];
        if ($result_json_data->status) {
            $data['authorization_url'] = $result_json_data->data->authorization_url;
            $data['reference'] = $result_json_data->data->reference;
            $response = ['success' => true, 'message' => __('Authorization URL created'), 'data' => $data];
        } else {
            $response = ['success' => false, 'message' => __('Authorization URL created is failed')];
        }

        return $response;
    }
}
