<?php

namespace App\Http\Services;

use App\Enums\CoinProvider;
use App\Enums\DepositeStatus;
use App\Enums\FundEnum;
use App\Enums\NetworkBase;
use App\Enums\YesNoStatus;
use App\Exceptions\InvalidRequestException;
use App\Facades\ResponseFacade;
use App\Http\Repositories\AdminCoinRepository;
use App\Http\Repositories\CoinSettingRepository;
use App\Http\Requests\UpdateWalletKeyRequest;
use App\Http\Resources\CoinResource;
use App\Http\Resources\FundTransferCoinListResponse;
use App\Jobs\BulkWalletGenerateJob;
use App\Model\Coin;
use App\Model\CoinNetwork;
use App\Model\CoinSetting;
use App\Model\CurrencyList;
use Illuminate\Http\Request;
use App\Model\WithdrawHistory;
use PragmaRX\Google2FA\Google2FA;
use App\Http\Services\BaseService;
use App\Model\DepositeTransaction;
use App\Model\Network;
use App\Model\Wallet;
use App\Services\FundTransferService\DataObject\FundTransferQueryParams;
use App\Services\FundTransferService\FundTransferService;
use App\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Services\BitCoinApiService;
use App\Http\Services\BitgoWalletService;
use Modules\IcoLaunchpad\Entities\IcoToken;
use Modules\IcoLaunchpad\Entities\IcoPhaseInfo;
use Modules\IcoLaunchpad\Entities\TokenBuyHistory;
use Nwidart\Modules\Facades\Module;

class CoinService extends BaseService
{

    protected AdminCoinRepository $object;
    public $model = Coin::class;
    public $repository = AdminCoinRepository::class;

    public function __construct()
    {
        parent::__construct($this->model, $this->repository);
    }

    public function getCoinTypeById(int $id): ?string
    {
        return $this->object->getCoinTypeById($id);
    }

    public function getCoin($data)
    {
        $object = $this->object->getDocs($data);

        if (empty($object)) {
            return null;
        }

        return $object;
    }

    public function getCoinListActive()
    {
        try {
            $data = $this->object->getCoinListActive();
            $response = ['success' => true, 'message' => __('Active Coin list!'), 'data' => $data];
        } catch (\Exception $e) {
            storeException("getCoinListActive", $e->getMessage());
            $response = ['success' => true, 'message' => __('Something went wrong!')];
        }
        return $response;
    }

    public function getPrimaryCoin()
    {
        $coinRepo = new AdminCoinRepository($this->model);
        $object = $this->object->getPrimaryCoin();

        return $object;
    }

    public function getBuyableCoin()
    {
        $object = $this->object->getBuyableCoin();
        if (empty($object)) {
            return null;
        }

        return json_encode($object);
    }

    public function getBuyableCoinDetails($coinId)
    {
        $object = $this->object->getBuyableCoinDetails($coinId);
        if (empty($object)) {
            return null;
        }
        return json_encode($object);
    }

    public function generate_address($coinId)
    {
        $address = '';

        $coinApiCredential = $this->object->getCoinApiCredential($coinId);
        if (isset($coinApiCredential)) {
            $api = new BitCoinApiService($coinApiCredential->user, decryptId($coinApiCredential->password), $coinApiCredential->host, $coinApiCredential->port);
            $address = $api->getNewAddress();
        }

        return json_encode($address);
    }

    public function getCoinApiCredential($coinId)
    {
        $coinRepo = new AdminCoinRepository($this->model);
        $object = $coinRepo->getCoinApiCredential($coinId);
        if (empty($object)) {
            return null;
        }
        return $object;
    }

    public function addNewCoin($request)
    {
        $data = [
            'currency_type' => $request->currency_type,
            'name' => $request->name,
            'coin_type' => strtoupper($request->coin_type),
            'network' => 0,
            'decimal' => $request->decimal ? intval($request->decimal) : 18,
        ];
        if ($request->currency_type == CURRENCY_TYPE_FIAT) {
            if ($currency = CurrencyList::whereCode($request->coin_type)->first()) {
                $data['currency_id'] = $currency->id;
                $data['coin_price'] = bcdivx(1, $currency->rate, 8);
            }
        } else {
            if ($request->get_price_api == YesNoStatus::YES->value) {
                $pair = strtoupper($request->coin_type) . '_' . 'USDT';
                $apiData = getPriceFromApi($pair);

                if (!$apiData['success'])
                    return failed(__('Get api data failed, please add manual price'));

                $data['coin_price'] = $apiData['data']['price'];
            } else {
                $data['coin_price'] = $request->coin_price;
            }

            $data['active_provider'] = $request->active_provider;
        }
        $coin = Coin::create($data);

        BulkWalletGenerateJob::dispatch($coin->id, WALLET_GENERATE_BY_COIN);

        return success(__('New coin added successfully'));
    }

    public function updateCoin($request, int $coin_id)
    {
        $coinData = Coin::find($coin_id);
        if (empty($coinData))
            return failed(__('Coin not found'));

        $data = [
            'coin_type' => $request->coin_type,
            'name' => $request->name,
            'coin_price' => $request->coin_price,
            'minimum_buy_amount' => $request->minimum_buy_amount,
            'minimum_sell_amount' => $request->minimum_sell_amount,
            'minimum_withdrawal' => $request->minimum_withdrawal,
            'maximum_withdrawal' => $request->maximum_withdrawal,
            'withdrawal_fees' => $request->withdrawal_fees,
            'max_send_limit' => $request->max_send_limit ?? 0,
            'withdrawal_fees_type' => $request->withdrawal_fees_type ?? 2,
            'admin_approval' => $request->admin_approval ?? 2,
            'decimal' => $request->decimal ? intval($request->decimal) : 18,
            'active_provider' => $request->active_provider,
            'is_deposit' => $request->is_deposit ? 1 : 0,
            'is_withdrawal' => $request->is_withdrawal ? 1 : 0,
            'status' => $request->status ? 1 : 0,
            'trade_status' => $request->trade_status ? 1 : 0,
            'is_wallet' => $request->is_wallet ? 1 : 0,
            'is_buy' => $request->is_buy ? 1 : 0,
            'is_virtual_amount' => $request->is_virtual_amount ? 1 : 0,
            'is_currency' => $request->is_currency ? 1 : 0,
            'is_transferable' => $request->is_transferable ? 1 : 0,
            'is_demo_trade' => $request->is_demo_trade ? 1 : 0
        ];

        if (!empty($request->coin_icon)) {
            $icon = uploadFile($request->coin_icon, IMG_ICON_PATH, '');
            if ($icon != false)
                $data['coin_icon'] = $icon;
        }

        if ($coinData->active_provider != $data['active_provider']) {
            $providerType = CoinProvider::tryFrom($coinData->active_provider);
            $findNetwork = Network::select('id')->where('provider_type', $providerType->value ?? 0);
            if($providerType == CoinProvider::CUSTOM_RPC_NODE){
                $isThereAnyPendingTokens = DepositeTransaction::whereIn('network_id', $findNetwork)
                    ->whereIn("is_admin_receive", [DepositeStatus::PENDING->value, DepositeStatus::FAILED, DepositeStatus::PROCESSING])
                    ->where([
                        'coin_type' => $coinData->coin_type,
                        'address_type' => ADDRESS_TYPE_EXTERNAL,
                    ])->exists();

                if ($isThereAnyPendingTokens)
                    return $this->responseData(false, __('There are pending tokens, they must be accepted before changing network by RPC.'));
            }
            $pendingWithdrawal = WithdrawHistory::whereIn('network_id', $findNetwork)
                ->whereIn('status', [WithdrawHistory::PENDING, WithdrawHistory::FAILED, WithdrawHistory::PROCESSING])
                ->where([
                    'coin_type' => $coinData->coin_type,
                    'address_type' => ADDRESS_TYPE_EXTERNAL,
                ])->exists();

            if ($pendingWithdrawal)
                return $this->responseData(false, __('There are pending user withdrawal request, they must be accepted before changing network by rpc.'));
        }

        $this->object->updateCoin($coin_id, $data); // Update coin info
        return success(__('Coin updated successfully'));
    }

    public function getCoinDetailsById($coinId)
    {
        return $this->object->getCoinDetailsById($coinId);
    }

    // admin coin delete
    public function adminCoinDeleteProcess(int $coinId): array
    {
        try {
            if ($coin = Coin::find($coinId)) {
                if ($coin_network = CoinNetwork::where('currency_id', $coinId)->first())
                    return responseData(false, __("This coin has been merged with networks in coin network, So delete action aborted"));

                if ($coin->coin_type == 'BTC' || $coin->coin_type == 'USDT')
                    return responseData(false, __('You never delete this coin, because this is on of the base coin '));

                $check = $this->checkCoinDeleteCondition($coin);
                if ($check['success'] == true) {
                    $coin->delete();
                    Wallet::where(['coin_id' => $coin->id])->delete();
                    return responseData(true, __('Coin deleted successfully'));
                }
                return responseData(false, $check['message']);
            }
            return responseData(false, __("Coin not found"));
        } catch (\Exception $e) {
            storeException("adminCoinDeleteProcess", $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function deleteWebhook($service, $coin, $request)
    {
        $result = $service->removeWalletWebhook($coin->coin_type, $coin->bitgo_wallet_id, $request->type, $coin->bitgo_webhook_url, $coin->bitgo_webhook_id);

        if ($result["success"] == false)
            throw new InvalidRequestException($result["message"]);

        return $this->responseData(true, $result["message"], $result["data"]);
    }

    // add webhook
    public function webhookSaveProcess($request)
    {
        $coin = (new CoinSettingRepository())->getCoinSettingData(decrypt($request->coin_id), NetworkBase::BITGO_API->value);

        if (empty($coin))
            return failed(__('Coin not found'));

        if (empty($coin->bitgo_wallet_id))
            return failed(__("Your Bitgo wallet id not set yet !!"));

        $bitgoApi = new BitgoWalletService();
        if (($request->url !== $coin->bitgo_webhook_url || $request->numConfirmations !== $coin->bitgo_webhook_numConfirmations) && !empty($coin->bitgo_webhook_url))
            return $this->deleteWebhook($bitgoApi, $coin, $request);

        $allToken = $request->allToken == 1 ? true : false;
        $bitgoResponse = $bitgoApi->addWebhook($coin->coin_type, $coin->bitgo_wallet_id, $request->type, $allToken, $request->url, $request->label, intval($request->numConfirmations));

        if (!is_success($bitgoResponse))
            return failed($bitgoResponse['message']);

        CoinSetting::where(['coin_id' => decrypt($request->coin_id), 'network' => NetworkBase::BITGO_API->value])->update([
            'bitgo_webhook_label' => $request->label,
            'bitgo_webhook_type' => $request->type,
            'bitgo_webhook_url' => $request->url,
            'bitgo_webhook_numConfirmations' => $request->numConfirmations,
            'bitgo_webhook_allToken' => $request->allToken,
            'bitgo_webhook_id' => $bitgoResponse['data']["id"],
            'webhook_status' => STATUS_ACTIVE
        ]);
        return success(__('Webhook updated successful'), $bitgoResponse);
    }

    public function saveCoinByICO($ico_id, $data)
    {
        try {
            $response = $this->object->saveCoinByICO($ico_id, $data);
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException('saveCoinByICO', $e->getMessage());
        }
        return $response;
    }

    public function makeTokenListedToCoin($coin_id)
    {
        $check_module = Module::allEnabled();

        if (!empty($check_module) && (isset($check_module['IcoLaunchpad']) && $check_module['IcoLaunchpad'] == 'IcoLaunchpad')) {
            $coin_details = Coin::find($coin_id);

            if (isset($coin_details)) {
                $token_details = IcoToken::find($coin_details->ico_id);
                if (isset($token_details)) {
                    $pending_token_buy_history_list = TokenBuyHistory::where('token_id', $token_details->id)
                        ->where('status', STATUS_PENDING)->get();

                    if ($pending_token_buy_history_list->count() > 0) {
                        return responseData(false, __('Please, Accept or Reject the pending token buy history, and then try again!'));
                    } else {
                        $ico_phase_list = IcoPhaseInfo::where('ico_token_id', $token_details->id)->get();

                        if ($ico_phase_list->count() == 0) {
                            return responseData(false, __('You can not make this coin listed because this token has no phase!'));
                        }
                        IcoPhaseInfo::where('ico_token_id', $token_details->id)->where('status', STATUS_ACTIVE)->update(['status' => STATUS_INACTIVE]);

                        $coin_details->is_listed = STATUS_ACTIVE;
                        $coin_details->is_withdrawal = STATUS_ACTIVE;
                        $coin_details->is_deposit = STATUS_ACTIVE;
                        $coin_details->is_buy = STATUS_ACTIVE;
                        $coin_details->is_sell = STATUS_ACTIVE;
                        $coin_details->is_listed = STATUS_ACTIVE;
                        $coin_details->trade_status = STATUS_ACTIVE;
                        $coin_details->save();
                        BulkWalletGenerateJob::dispatch($coin_details->id, WALLET_GENERATE_BY_COIN);
                        return responseData(true, __('Your ICO Token is listed Successfully!'));
                    }
                }
                return responseData(false, __('ICO Token not found!'));
            } else {
                return responseData(false, __('Invalid Request!'));
            }
        } else {

            return responseData(false, __('Your ICO module is not enabled!'));
        }
    }

    public function getAllActiveCoinList()
    {
        $coin_list = Coin::where('status', '<>', STATUS_DELETED)
            ->where('ico_id', '=', 0)
            ->orWhere('is_listed', STATUS_ACTIVE)->orderBy('id', 'asc')->get();

        $response = ['success' => true, 'message' => __('Active Coin List'), 'data' => $coin_list];

        return $response;
    }

    public function updateWalletKey(Request $request, User $user)
    {
        $id = decrypt($request->id);

        if (checkGoogleAuth()) {
            if (empty($request->code))
                throw new InvalidRequestException(__('Google authenticator code is missing!'));

            if (blank($user->google2fa_secret ?? null))
                throw new InvalidRequestException(__('Google authenticator not setup'));

            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey($user->google2fa_secret, $request->code);
            if (empty($valid))
                throw new InvalidRequestException(__('Google authentication code is invalid'));
        }

        if (!Hash::check($request->password, $user->password))
            throw new InvalidRequestException(__('Invalid Password!'));

        $coinSettingDetails = CoinSetting::find($id);
        if (empty($coinSettingDetails))
            throw new InvalidRequestException(__('Coin Settings not found!'));

        $coinSettingDetails->update([
            'wallet_address' => $request->wallet_address,
            'wallet_key' => encrypt($request->wallet_key)
        ]);

        return $this->responseData(true, 'Wallet Key is updated successfully!');
    }

    public function viewWalletKey(Request $request, User $user)
    {
        if (checkGoogleAuth()) {
            if (empty($request->google_authenticator))
                throw new InvalidRequestException(__('Google authenticator code is missing!'));

            if (blank($user->google2fa_secret ?? null))
                throw new InvalidRequestException(__('Google authenticator not setup'));

            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey($user->google2fa_secret, $request->google_authenticator);
            if (!$valid)
                throw new InvalidRequestException(__('Google authentication code is invalid'));
        }
        if (empty($request->id))
            throw new InvalidRequestException(__('Invalid Request!'));

        if (empty($request->password))
            throw new InvalidRequestException(__('Enter Your Password'));

        if (!Hash::check($request->password, $user->password))
            throw new InvalidRequestException(__('Invalid Password!'));

        $coinSettingDetails = CoinSetting::find(decrypt($request->id));
        if (empty($coinSettingDetails->wallet_key))
            throw new InvalidRequestException(__('Wallet key not found!'));

        $wallet_key = '';
        try {
            $wallet_key = decrypt($coinSettingDetails->wallet_key);
        } catch (Exception $e) {
            storeLog(processExceptionMsg($e), "error");
        }

        return $this->responseData(true, __('System Wallet Private Key details'), $wallet_key);
    }

    public function checkCoinDeleteCondition($coin)
    {
        $checkCoinWalletAddress = checkWalletAddressByCoin($coin->coin_type);
        if ($checkCoinWalletAddress > 0)
            return failed(__('This coin wallet already have some address, so you should not delete this coin'));

        $checkCoinPair = checkPairByCoin($coin->id);
        if ($checkCoinPair > 0)
            return failed(__('This coin already have coin pair, so first delete that pair then try again'));

        $checkCoinDeposit = checkDepositByCoin($coin->coin_type);
        if ($checkCoinDeposit > 0)
            return failed(__('This coin already have some deposit, so you should not delete this coin'));

        $checkCoinWithdrawal = checkWithdrawalByCoin($coin->coin_type);
        if ($checkCoinWithdrawal > 0)
            return failed(__('This coin already have some withdrawal, so you should not delete this coin'));

        return success();
    }

    /**
     * This Method Return Coin List For Deposit, Withdrawal And Check Deposit Page On Frontend
     * @param \Illuminate\Http\Request $request
     * @return array{data: mixed, message: string, success: bool}
     */
    public function getCoinsList(Request $request)
    {
        $coins = Coin::select( "id", "name", "coin_type", "coin_icon", "network", "coin_price" )
            ->where('status', STATUS_ACTIVE);

        if ($request->currency_type)
            $coins->where('currency_type', $request->currency_type);

        if ($request->is_deposit) {
            $coins->where('is_deposit', STATUS_ACTIVE);

            // 1st take coin network table data
            // 2nd take network table data
            // 3rd check base type, is deposit checkable
            if ($request->check_deposit){
                $coins->with(['coin_network:id,network_id', 'coin_network.network:id,name,base_type'])
                ->where('active_provider', CoinProvider::CUSTOM_RPC_NODE->value)
                ->whereHas('coin_network.network', function($query){
                        return $query->whereIn('base_type', array_keys(CoinProvider::depositCheckableRpcNode()));
                });
            }
        }

        if ($request->is_withdrawal)
            $coins->where('is_withdrawal', STATUS_ACTIVE);

        if ($request->trade_status)
            $coins->where('trade_status', STATUS_ACTIVE);

        if ($request->transfer)
            return $this->getFundTransferData($request);

        if($coins = $coins->get())
            return success(__("All coin get successfully"), CoinResource::collection($coins));
        return failed(__("Coins failed to get"));
    }

        /**
     * make query array for fund transfer page coin list data
     * @param Request $request
     * @return mixed
     */
    protected function getFundTransferData(Request $request): mixed
    {
        // TODO FIL: fix fund transfer coin list query on evm branch
        $service = new FundTransferService();
        $fromFundType = FundEnum::tryFrom($request->from_fund_type ?? 0);
        $toFundType = FundEnum::tryFrom($request->to_fund_type ?? 0);

        if (!$fromFundType)
            return failed(__("Invalid from fund type"));
        if (!$toFundType)
            return failed(__("Invalid to fund type"));
        if ($fromFundType == $toFundType)
            return failed(__("From and to fund type can't be same"));

        $fundCoinsQueryByFundType = match (true) {
            $fromFundType == FundEnum::FUTURE || $toFundType == FundEnum::FUTURE => FundEnum::FUTURE,
            $fromFundType == FundEnum::P2P || $toFundType == FundEnum::P2P => FundEnum::P2P,
            default => FundEnum::SPOT,
        };

        $coins = $service->getCoinsByFund(
            fundType: $fundCoinsQueryByFundType,
            params: new FundTransferQueryParams(
                user_id: authId(),
                orderBy: $request->order_by ?? 'balance',
                direction: $request->direction ?? 'desc'
            )
        );

        return success(
            __("Coins data get successfully"),
            FundTransferCoinListResponse::collection($coins)
        );
    }
}
