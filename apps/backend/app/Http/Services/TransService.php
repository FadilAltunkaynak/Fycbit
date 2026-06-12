<?php

namespace App\Http\Services;

use App\Enums\DepositeStatus;
use App\Enums\FeesType;
use App\Enums\NetworkBase;
use App\Enums\WithdrawStatus;
use App\Exceptions\HttpResponseException;
use App\Facades\ResponseFacade;
use App\Http\Repositories\CoinSettingRepository;
use App\Http\Repositories\WalletRepository;
use App\Jobs\WithdrawalProcessJob;
use App\Model\CoinPaymentNetworkFee;
use App\Model\CoinSetting;
use App\Traits\NumberFormatTrait;
use App\Traits\ResponseFormatTrait;
use App\User;
use Carbon\Carbon;
use App\Model\Coin;
use App\Model\Wallet;
use App\Jobs\MailSend;
use App\Jobs\Withdrawal;
use App\Model\CoinNetwork;
use App\Model\TempWithdraw;
use App\Model\WalletCoUser;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Model\WalletNetwork;
use App\Model\WithdrawHistory;
use PragmaRX\Google2FA\Google2FA;
use App\Model\DepositeTransaction;
use Illuminate\Support\Facades\DB;
use App\Model\WalletAddressHistory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Services\MyCommonService;
use function PHPUnit\Framework\isNull;
use App\Model\CoWalletWithdrawApproval;
use App\Enums\CoinPaymentActiveVersion;
use App\Http\Repositories\AffiliateRepository;
use App\Jobs\DistributeWithdrawalReferralBonus;
use App\Http\Services\Evm\EvmWalletService;
use App\Http\Services\Solana\AddressValidator;
use App\Model\Network;
use App\Exceptions\InvalidRequestException;
use Azimo\Apple\Api\Exception\InvalidResponseException;

class TransService
{
    use ResponseFormatTrait, NumberFormatTrait;

    protected $logger;
    private AddressValidator $addressValidator;

    public function __construct()
    {
        $this->logger = new Logger();
        $this->addressValidator = app()->make(AddressValidator::class);
    }

    private function generate_email_verification_key()
    {
        do {
            $key = Str::random(60);
        } while (User::where('email_verified', $key)->count() > 0);

        return $key;
    }

    // make withdrawal data
    private function makeWithdrawalData(array $data, string $trans_id)
    {
        return [
            'wallet_id' => $data['wallet']->id,
            'address' => $data['address'],
            'amount' => $data['amount'],
            'address_type' => $data['addressType'],
            'fees' => $data['fees'],
            'coin_type' => $data['coin']->coin_type,
            'transaction_hash' => $trans_id,
            'confirmations' => 0,
            'status' => WithdrawStatus::INITIAL->value,
            'message' => $data['note'],
            'receiver_wallet_id' => @$data['receiverWallet']->id,
            'user_id' => $data['user']->id,
            'network_type' => $data['network_type'] ?? '',
            'memo' => $data['memo'] ? $data['memo'] : '',
            'network_id' => $data['coinNetwork']->network_id ?? 0
        ];
    }
    // withdrawal process from job
    public function startWithdrawalProcess($data): array
    {
        DB::beginTransaction();
        $user = $data['user'];
        $coin = $data['coin'];
        $coinNetwork = $data['coinNetwork'];
        $wallet = $data['wallet'];
        $wallet_id = $wallet->id ?? 0;

        $wallet = Wallet::lockForUpdate()->find($wallet_id);

        $validateData = $this->checkWithdrawalValidation($data['address'], $data['amount'], $data['userId'], $coin, $coinNetwork, $wallet, $data['memo'])['data'];
        $data = array_merge($data, $validateData);

        $trans_id = Str::random(32); // we make this same for deposit and withdrawal

        $sendAmount = bcaddx($data['amount'], $data['fees'], 8);

        try {
            $wallet->decrement('balance', $sendAmount);

            $transaction = WithdrawHistory::create($this->makeWithdrawalData($data, $trans_id));

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            storeLog(processExceptionMsg($e));
            return failed($e->getMessage());
        }
        return match ($validateData['addressType']) {
            ADDRESS_TYPE_INTERNAL => $this->processInternalWithdrawal($transaction, $trans_id, $data),
            ADDRESS_TYPE_EXTERNAL => $this->processExternalWithdrawal($transaction, $coin, $data['amount']),
        };
    }

    /**
     * Summary of processInternalWithdrawal
     * @param WithdrawHistory $transaction
     * @param string $trans_id
     * @param array $data
     * @return array
     */
    public function processInternalWithdrawal(WithdrawHistory $transaction, string $trans_id, array $data): array
    {
        DB::beginTransaction();
        try {
            DepositeTransaction::create($this->makeDepositData($data, $trans_id));

            $transaction->update(['status' => WithdrawStatus::SUCCESS->value]);

            $data['receiverWallet']->increment('balance', $data['amount']);

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();

            $transaction->update([
                'status' => WithdrawStatus::FAILED->value,
                'reject_note' => $e->getMessage(),
            ]);
            storeLog(processExceptionMsg($e));
            return failed($e->getMessage());
        }
        return success(__('Internal withdrawal process success'));
    }
    /**
     * Summary of processExternalWithdrawal
     * @param WithdrawHistory $transaction
     * @param Coin $coin
     * @param float $amount
     * @return array
     */
    public function processExternalWithdrawal(WithdrawHistory $transaction, Coin $coin, float $amount)
    {
        if (checkCryptoAdminApproval($amount, $coin->id) || defined("IS_PUBLIC_API")) {
            // If the need admin approval, the transaction status is set to PENDING
            $transaction->update(['status' => WithdrawStatus::PENDING->value]);
            return success(__('External withdrawal process goes to admin approval'));
        }
        $transaction->update(['status' => WithdrawStatus::PROCESSING->value]);

        return $this->acceptPendingExternalWithdrawal($transaction);
    }


    //make deposit data
    public function makeDepositData(array $data, string $trans_id)
    {
        return [
            'address' => $data['address'],
            'address_type' => $data['addressType'],
            'amount' => $data['amount'],
            'fees' => $data['fees'],
            'coin_type' => $data['coin']->coin_type,
            'transaction_id' => $trans_id,
            'confirmations' => 0,
            'status' => DepositeStatus::SUCCESS->value,
            'sender_wallet_id' => $data['wallet']->id,
            'receiver_wallet_id' => $data['receiverWallet']->id,
            'network_type' => $data['network_type'] ?? '',
            'network_id' => $data['network_id'] ?? ''
        ];
    }
    // check internal address
    private function isInternalAddress($address, $coin, $memo = null)
    {
        $checkAddressQuery = WalletAddressHistory::where(['address' => $address, "coin_type" => $coin])->with('wallet');
        if ($memo) $checkAddressQuery->where('memo', $memo);
        $checkAddress = $checkAddressQuery->first();
        if ($checkAddress) {
            return $checkAddress;
        } else {
            return WalletNetwork::where('address', $address)->with('wallet')->first();
        }
    }

    // cancel transaction
    private function _cancelTransaction($user, $wallet, $address, $amount, $pendingTransaction)
    {
        if (!empty($pendingTransaction)) {
            $pendingTransaction->status = STATUS_REJECTED;
            $pendingTransaction->update();
        }
        //  $mailService = app(MailService::class);
        $userName = $user->first_name . ' ' . $user->last_name;
        $userEmail = $user->email;
        $companyName = settings("company_name") ?? __('Company Name');
        $subject = __(':emailSubject | :companyName', ['emailSubject' => __('Send coin failure'), 'companyName' => $companyName]);
        $data['user'] = $user;
        $data['amount'] = $amount;
        $data['address'] = $address;
        $data['wallet'] = $wallet;
        //  $mailService->send('email.send_coin_failure', $data, $userEmail, $userName, $subject);
    }





    private function calculate_fees($amount)
    {
        return $amount;
    }


    private function sendTransactionMail($sender_user, $mailTemplet, $receiver_user, $amount, $emailSubject)
    {
        $mailService = app(MailService::class);
        $userName = $sender_user->first_name . ' ' . $sender_user->last_name;
        $userEmail = $sender_user->email;
        $companyName = settings("company_name") ?? __('Company Name');
        $subject = __(':emailSubject | :companyName', ['emailSubject' => $emailSubject, 'companyName' => $companyName]);
        $data['data'] = $sender_user;
        $data['anotherUser'] = $receiver_user;
        $data['amount'] = $amount;
        $mailService->send($mailTemplet, $data, $userEmail, $userName, $subject);
    }

    private function sendExternalTransactionMail($sender_user, $mailTemplet, $address, $amount, $emailSubject)
    {
        $mailService = app(MailService::class);
        $userName = $sender_user->first_name . ' ' . $sender_user->last_name;
        $userEmail = $sender_user->email;
        $companyName = settings("company_name") ?? __('Company Name');
        $subject = __(':emailSubject | :companyName', ['emailSubject' => $emailSubject, 'companyName' => $companyName]);
        $data['data'] = $sender_user;
        $data['address'] = $address;
        $data['amount'] = $amount;
        $mailService->send($mailTemplet, $data, $userEmail, $userName, $subject);
    }

    private function sendVerificationSms($phone, $randno)
    {
        $smsText = 'Your ' . allsetting()['app_title'] . ' verification code is here ' . $randno;
        app(SmsService::class)->send($phone, $smsText);
    }

    // user deposit history
    public function depositTransactionHistories($user_id = null, $status = null, $search = null, $order_by = null)
    {
        $histories = DepositeTransaction::join('wallets', 'wallets.id', 'deposite_transactions.receiver_wallet_id')
            ->select('wallets.*', 'deposite_transactions.*')
            ->when($user_id, function ($query) use ($user_id) {
                return $query->where('wallets.user_id', $user_id);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('deposite_transactions.status', $status);
            })
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('deposite_transactions.address', 'like', "%$search%")
                        ->orWhere('deposite_transactions.transaction_id', 'like', "%$search%")
                        ->orWhere('deposite_transactions.coin_type', 'like', "%$search%")
                        ->orWhere('deposite_transactions.amount', 'like', "%$search%");
                });
            })
            ->when(!empty($order_by['column_name']) && !empty($order_by['order_by']), function ($query) use ($order_by) {
                $withdraw_columns = ['created_at', 'address', 'amount', 'fees', 'coin_type'];
                if (in_array($order_by['column_name'], $withdraw_columns)) {
                    return $query->orderBy("deposite_transactions.$order_by[column_name]", $order_by['order_by']);
                }
                return $query->orderBy("wallets.$order_by[column_name]", $order_by['order_by']);
            });

        return $histories;
    }

    // user withdrawal history
    public function withdrawTransactionHistories($user_id = null, $status = null, $search = null, $order_by = null)
    {
        $histories = WithdrawHistory::join('wallets', 'wallets.id', 'withdraw_histories.wallet_id')
            ->select('wallets.*', 'withdraw_histories.*')
            ->when($user_id, function ($query) use ($user_id) {
                return $query->where('wallets.user_id', $user_id);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('withdraw_histories.status', $status);
            })
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('withdraw_histories.address', 'like', "%$search%")
                        ->orWhere('withdraw_histories.transaction_hash', 'like', "%$search%")
                        ->orWhere('withdraw_histories.coin_type', 'like', "%$search%")
                        ->orWhere('withdraw_histories.amount', 'like', "%$search%");
                });
            })
            ->when(!empty($order_by['column_name']) && !empty($order_by['order_by']), function ($query) use ($order_by) {
                $withdraw_columns = ['created_at', 'address', 'amount', 'fees', 'coin_type'];
                if (in_array($order_by['column_name'], $withdraw_columns)) {
                    return $query->orderBy("withdraw_histories.$order_by[column_name]", $order_by['order_by']);
                }
                return $query->orderBy("wallets.$order_by[column_name]", $order_by['order_by']);
            });

        return $histories;
    }

    public function isAllApprovalDoneForCoWalletWithdraw($tempWithdraw)
    {
        if (empty($tempWithdraw)) {
            Log::warning('Empty temp withdrawal.');
            return ['success' => false, 'message' => __('Invalid withdrawal.')];
        }
        $response = $this->approvalCounts($tempWithdraw);
        if ($response['alreadyApprovedUserCount'] >= $response['requiredUserApprovalCount']) {
            $tempWithdraw->status = STATUS_ACCEPTED;
            try {
                if (!$tempWithdraw->save())
                    throw new \Exception(__('Temp withdraw status success save failed'));
                return ['success' => true, 'message' => ''];
            } catch (\Exception $e) {
                Log::warning($e->getMessage());
                return ['success' => false, 'message' => $e->getMessage()];
            }
        } else
            return ['success' => false, 'message' => __('Not enough approval done yet.')];
    }

    public function approvalCounts($tempWithdraw)
    {
        $userPercentageForApproval = settings(CO_WALLET_WITHDRAWAL_USER_APPROVAL_PERCENTAGE_SLUG);
        $userPercentageForApproval = !empty($userPercentageForApproval) ? $userPercentageForApproval : 60;
        $coUserCount = WalletCoUser::where(['wallet_id' => $tempWithdraw->wallet_id])->count();
        $requiredUserApprovalCount = ceil($coUserCount * ($userPercentageForApproval / 100.0));
        $alreadyApprovedUserCount = CoWalletWithdrawApproval::where(['temp_withdraw_id' => $tempWithdraw->id])->count();
        return ['requiredUserApprovalCount' => $requiredUserApprovalCount, 'alreadyApprovedUserCount' => $alreadyApprovedUserCount];
    }

    /**
     * Summary of checkWithdrawalValidation
     * @param string $address
     * @param float $amount
     * @param int $userId
     * @param Coin $coin
     * @param CoinNetwork $coinNetwork
     * @param Wallet $wallet
     * @return array
     * @throws \App\Exceptions\HttpResponseException
     */
    public function checkWithdrawalValidation(string $address, float $amount, int $userId, Coin $coin, CoinNetwork $coinNetwork, Wallet $wallet, mixed $memo = null)
    {
        $data = [
            'fees' => 0,
            'receiverWallet' => null,
            'addressType' => ADDRESS_TYPE_EXTERNAL,
            'min' => $coin->minimum_withdrawal,
            'max' => $coin->maximum_withdrawal,
            'amount' => $this->truncateNum($amount),
            'fees_percentage' => $this->truncateNum($coinNetwork->withdrawal_fees),
            'fees_type' => $coinNetwork->withdrawal_fees_type,
        ];
        $walletAddress = $this->isInternalAddress($address, $coin->coin_type, $memo);
        if ($walletAddress) {
            if ($walletAddress->wallet->user_id == $userId)
                ResponseFacade::failed(__('You can not send to your own wallet!'))->safeThrow();

            if ($walletAddress->wallet->coin_type != $coin->coin_type)
                ResponseFacade::failed(__('Both wallet coin type should be same'))->safeThrow();

            $data['receiverWallet'] = $walletAddress->wallet;
            $data['addressType'] = ADDRESS_TYPE_INTERNAL;
        } else {
            $fees = FeesType::calculateFees($coinNetwork->withdrawal_fees_type, $amount, $coinNetwork->withdrawal_fees);
            storeLog("fees:  $fees");
            $data['fees'] = $this->truncateNum($fees);
        }

        $this->checkWithdrawalCoinStatus($coin, $wallet, $amount, $data['fees']);

        return success($data);
    }

    /**
     * Summary of checkWithdrawalCoinStatus
     * @param Coin $coin
     * @param Wallet $wallet
     * @param float $amount
     * @param float $fees
     * @return array
     * @throws \App\Exceptions\HttpResponseException
     */
    public function checkWithdrawalCoinStatus(Coin $coin, Wallet $wallet, float $amount, float $fees)
    {
        if ($coin->status != STATUS_ACTIVE)
            ResponseFacade::failed(__(
                ':coin_type both wallet coin type should be same',
                ['coin_type' => $coin->coin_type]
            ))->safeThrow();

        if ($coin->is_withdrawal != STATUS_ACTIVE)
            ResponseFacade::failed(__(
                ':coin_type coin is not available for withdrawal right now',
                ['coin_type' => $coin->coin_type]
            ))->safeThrow();

        if (($amount + $fees) < $coin->minimum_withdrawal)
            ResponseFacade::failed(__(
                'Minimum withdrawal amount :amount',
                ['amount' => $coin->minimum_withdrawal . ' ' . $coin->coin_type]
            ))->safeThrow();

        if (($amount + $fees) > $coin->maximum_withdrawal)
            ResponseFacade::failed(__(
                'Maximum withdrawal amount :amount',
                ['amount' => $coin->maximum_withdrawal . ' ' . $coin->coin_type]
            ))->safeThrow();

        if ($wallet->balance < ($amount + $fees))
            ResponseFacade::failed(__(
                "Insufficient balance for withdrawal"
            ))->safeThrow();

        return success(__('Coin status check passed'));
    }

    /**
     * Summary of acceptPendingExternalWithdrawal
     * @param WithdrawHistory $transaction
     * @param int|null $adminId
     * @return array
     * @throws HttpResponseException
     */
    public function acceptPendingExternalWithdrawal(WithdrawHistory $transaction, ?int $adminId = null): array|HttpResponseException
    {
        try {
            $gasFees   = 0;
            $transaction?->load("network");
            $base_type = $transaction?->getRelationValue("network")?->base_type;

            if (NetworkBase::isCoinPayment($base_type)) {
                $currency = $transaction->network_type ?? $transaction->coin_type;
                $networkFees = CoinPaymentNetworkFee::where(['coin_type' => $currency])->first()?->tx_fee ?? 0;
                $amountWithNetworkFees = bcaddx($transaction->amount, $networkFees, 8);

                $coinPaymentVersion = CoinPaymentActiveVersion::tryFrom(settings('COIN_PAYMENT_VERSION') ?? 0);
                if(!$coinPaymentVersion) return failed(__("CoinPayment version invalid"));

                $coinPaymentService = $coinPaymentVersion->getService();
                $response = match($coinPaymentVersion){
                    CoinPaymentActiveVersion::LEGACY => $coinPaymentService->CreateWithdrawal(
                        amount: $amountWithNetworkFees,
                        currency: $currency,
                        address: $transaction->address,
                        dest_tag: $transaction->memo
                    ),
                    CoinPaymentActiveVersion::COIN_PAYMENT_V2 => $coinPaymentService->CreateWithdrawal(
                        $transaction->wallet_id,
                        $amountWithNetworkFees,
                        $transaction->network_type ?: $transaction->coin_type,
                        $transaction->address,
                        dest_tag: $transaction?->memo ?? ''
                    )
                };

                if ($response['error'] != 'ok')
                    throw new Exception($response['error']);

                $transactionHash = $response['result']['id'];

            } elseif (NetworkBase::isBitcoin($base_type)) {

                $response = $this->sendCoinWithBitCoin($transaction, $adminId, empty($adminId), $transaction->user_id);

                if (!is_success($response))
                    throw new Exception($response['message']);

                $transactionHash = $response['transaction_id'];

            } elseif (NetworkBase::isBitgo($base_type)) {
                $response = $this->sendCoinWithBitgo($transaction);
                if (!is_success($response))
                    throw new Exception($response['message']);

                $transactionHash = $response['data'];

            } elseif (NetworkBase::isCustomNetwork($base_type)) {
                $response = $this->sendTokenWithEvm($transaction, $adminId);
                if (!is_success($response))
                    throw new Exception($response['message']);

                $response = $response['data'];
                $transactionHash = $response['transaction_id'];
                $gasFees = $response['used_gas'];

            } else
                throw new Exception(__('No Api found'));

            return $this->completeWithdrawalTransaction($transaction, $transactionHash, $gasFees, $adminId);

        } catch (Exception $e) {
            $transaction->update(['status' => WithdrawStatus::FAILED->value, 'reject_note' => $e->getMessage()]);
            ResponseFacade::failed($e->getMessage())->safeThrow();
        }
    }

    /**
     * Summary of completeWithdrawalTransaction
     * @param WithdrawHistory $transaction
     * @param string $transactionHash
     * @param float $gasFees
     * @param int|null $adminId
     * @param WithdrawStatus $status
     * @return array
     */
    public function completeWithdrawalTransaction(WithdrawHistory $transaction, string $transactionHash, float $gasFees = 0, ?int $adminId = null, WithdrawStatus $status = WithdrawStatus::SUCCESS): array
    {
        $transaction->update([
            'transactionHash' => $transactionHash,
            'used_gas' => $gasFees,
            'status' => $status->value,
            'updated_by' => $adminId,
        ]);
        DistributeWithdrawalReferralBonus::dispatch($transaction)->onQueue('referral');

        // Send email notification to the user
        // $title = __("Withdraw request approved by system");
        // $body = __("Your withdrawal request is approved by System. \nWithdrawal transaction hash is $transaction_hash.");
        // $this->sendEmailAndNotification($title, $body, $transaction->user);

        return success(
            empty($adminId)
            ? __('User withdrawal processed successfully')
            : __('Pending withdrawal accepted Successfully')
        );
    }

    // external transfer by using bit coin api
    public function sendCoinWithBitCoin(WithdrawHistory $transaction, $authId, $isAdmin, $user_id)
    {
        $coin = (new CoinSettingRepository(CoinSetting::class))
            ->getCoinSettingData($transaction->coin_type, $transaction->network_id);

        if (empty($coin))
            return failed(__('Coin not found'));

        $api = new BitCoinApiService($coin->coin_api_user, decryptId($coin->coin_api_pass), $coin->coin_api_host, $coin->coin_api_port);
        $response = $api->verifyAddress($transaction->address);

        if (empty($response))
            return failed(__('Invalid address!'));

        $adminId = $isAdmin ? $authId : null;
        $userId = $isAdmin ? $user_id : $authId;

        $transaction_id = $api->sendToAddress($transaction->address, $transaction->amount, $userId, $adminId);

        if (empty($transaction_id))
            return failed(__('Failed to send coin!'));

        return success($transaction_id);
    }

    /**
     * Summary of withdrawalProcess
     * @param \Illuminate\Http\Request $request
     * @param int $userId
     * @return array
     */
    public function withdrawalProcess(Request $request, int $userId): array
    {
        $response = $this->preWithdrawalProcess($request, $userId, true)['data'];

        $data = [
            ...$response,
            'user' => $request->user(),
            'userId' => $userId,
            'address' => $request->address,
            'amount' => $request->amount,
            'note' => $request->note ?? null,
            'memo' => $request->memo ?? null,
            'network_type' => $request->network_type ?? null,
        ];
        if (isset($request->code) && !defined("IS_PUBLIC_API")) {
            $request->merge(['code_type' => GOOGLE_AUTH]);
            $checkTwoFactor = (new User2FAService)->checkTwoFactor($request, "two_factor_withdraw", $userId);
            if (!is_success($checkTwoFactor))
                ResponseFacade::result($checkTwoFactor)->safeThrow();
        }

        WithdrawalProcessJob::dispatch($data)->onQueue('withdrawal');

        $message = checkCryptoAdminApproval($request->amount, $data['coin']->id)
            ? __('Withdrawal process started successfully. Please wait for admin approval')
            : __('Withdrawal process started successfully. We will notify you the result soon');

        return success($message);
    }

    // kyc validation check
    public function kycValidationCheck($userId)
    {
        $response = [
            'success' => true,
            'message' => __('success ')
        ];
        if (settings('kyc_enable_for_withdrawal') == STATUS_ACTIVE) {
            if (settings('kyc_nid_enable_for_withdrawal') == STATUS_ACTIVE) {
                $checkNid = checkUserKyc($userId, KYC_NID_REQUIRED, __('withdrawal '));
                if ($checkNid['success'] == false) {
                    $response = [
                        'success' => false,
                        'message' => $checkNid['message']
                    ];
                    return $response;
                } else {
                    $response = [
                        'success' => true,
                        'message' => __('success ')
                    ];
                }
            }
            if (settings('kyc_passport_enable_for_withdrawal') == STATUS_ACTIVE) {
                $checkPass = checkUserKyc($userId, KYC_PASSPORT_REQUIRED, __('withdrawal '));
                if ($checkPass['success'] == false) {
                    $response = [
                        'success' => false,
                        'message' => $checkPass['message']
                    ];
                    return $response;
                } else {
                    $response = [
                        'success' => true,
                        'message' => __('success ')
                    ];
                }
            }
            if (settings('kyc_driving_enable_for_withdrawal') == STATUS_ACTIVE) {
                $checkDrive = checkUserKyc($userId, KYC_DRIVING_REQUIRED, __('withdrawal '));
                if ($checkDrive['success'] == false) {
                    $response = [
                        'success' => false,
                        'message' => $checkDrive['message']
                    ];
                    return $response;
                } else {
                    $response = [
                        'success' => true,
                        'message' => __('success ')
                    ];
                }
            }
        } else {
            $response = [
                'success' => true,
                'message' => __('success ')
            ];
        }

        return $response;
    }

    // send coin with bitgo
    public function sendCoinWithBitgo($transaction)
    {
        $coin = (new CoinSettingRepository(CoinSetting::class))
            ->getCoinSettingData($transaction->coin_type, $transaction->network_id);

        if (empty($coin))
            return failed(__('Coin not found'));

        $bitgoService = new BitgoWalletService();
        $bitgoResponse = $bitgoService->sendCoinsWithBitgo(
            $coin->coin_type,
            $coin->bitgo_wallet_id,
            $transaction->amount,
            $transaction->address,
            decryptId($coin->bitgo_wallet)
        );
        if ($bitgoResponse['success'] == false)
            return failed($bitgoResponse['message']);

        rescue(function () use ($bitgoResponse){
            logger($bitgoResponse);
            info("BitGo Withdrawal Response");
        });

        $txid = data_get($bitgoResponse, 'data.txid')
            ?? data_get($bitgoResponse, 'data.transfer.txid')
            ?? data_get($bitgoResponse, 'data.transfer.coinSpecific.txid')
            ?? '';

        return success($txid);
    }

    /**
     * Summary of sendTokenWithEvm
     * @param WithdrawHistory $transaction
     * @param int|null $adminId
     * @return array
     */
    public function sendTokenWithEvm(WithdrawHistory $transaction, ?int $adminId = null): array
    {
        $requestData = [
            "id" => $transaction->id,
            "admin_id" => $adminId,
        ];
        $evm_service = new EvmWalletService();
        $result = $evm_service->withdrawalExternalApproval($requestData);

        return $result;
    }

    /**
     * Summary of preWithdrawalProcess
     * @param Request $request
     * @param int $userId
     * @param bool $finalProcess
     * @return array
     * @throws \App\Exceptions\HttpResponseException
     */
    public function preWithdrawalProcess(Request $request, int $userId, bool $finalProcess = false): array
    {
        if (isset($request->network_id)) {
            $coinNetwork = CoinNetwork::with(['coin', 'network'])->where([
                'currency_id' => $request->coin_id,
                'network_id' => $request->network_id,
                'status' => STATUS_ACTIVE
            ])->first();
        } else
            $coinNetwork = CoinNetwork::with(['coin', 'network'])->where(['currency_id' => $request->coin_id])->first();

        if (!$coinNetwork)
            ResponseFacade::failed(__('Coin network not found'))->safeThrow();

        $coin = $coinNetwork->coin;
        if (!$coin)
            ResponseFacade::failed(__('Coin not found'))->safeThrow();

        $network = $coinNetwork->network;
        if (!$network)
            ResponseFacade::failed(__('Network not found'))->safeThrow();

        if (
            NetworkBase::isCustomNetwork($network->base_type)
            && $coinNetwork->coin_decimal
            && $coinNetwork->coin_decimal < $this->countDecimalPlaces($request->amount)
        )
            ResponseFacade::failed(__(
                'Amount decimal should not exceed :decimal decimal',
                ['decimal' => $coinNetwork->coin_decimal]
            ))->safeThrow();

        if (
            NetworkBase::SOLANA_BASE_COIN->value == $network->base_type &&
            !$this->addressValidator->validateAddress($request->address)
        ) {
            ResponseFacade::failed(__('Invalid address'))->safeThrow();
        }

        $wallet = (new WalletRepository())->walletInfo($userId, $coin);
        if (!$wallet)
            ResponseFacade::failed(__('Wallet not found'))->safeThrow();

        $validateData = $this->checkWithdrawalValidation($request->address, $request->amount, $userId, $coin, $coinNetwork, $wallet, $request?->memo)['data'];

        if ($finalProcess) {
            $data = [
                'coin' => $coin,
                'network' => $network,
                'coinNetwork' => $coinNetwork,
                'wallet' => $wallet,
            ];
        } else {
            $data = [
                ...$validateData,
                'coin_type' => $coin->coin_type,
            ];
        }
        return success($data);
    }

    private function sendEmailAndNotification($title, $message, $user)
    {
        (new MyCommonService())->sendNotificationToUserUsingSocket(
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
            'mailTemplate' => emailTemplateName('genericemail')
        ];
        dispatch(new MailSend($emailData))->onQueue('send-mail');
    }
}
