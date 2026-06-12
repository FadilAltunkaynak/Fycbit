<?php


namespace App\Http\Services;

use App\Enums\DepositCollectionStatus;
use App\Enums\NetworkBase;
use App\Facades\ResponseFacade;
use App\Jobs\PendingDepositAcceptJob;
use App\Model\CoinNetwork;
use App\Model\DepositeTransaction;
use App\Http\Services\WalletService;
use App\Exceptions\InvalidRequestException;
use App\Http\Services\Evm\EvmWalletService;
use App\Traits\ResponseFormatTrait;
use Illuminate\Http\Request;

class DepositService
{
    use ResponseFormatTrait;

    public function __construct()
    {
    }

    // check deposit by transaction
    /**
     * Summary of checkDepositByHash
     * @param Request $request
     * @return array
     */
    public function checkDepositByHash(Request $request): array
    {
        $coinNetwork = CoinNetwork::with(['coin', 'network'])
            ->where(['currency_id' => $request->coin_id, 'network_id' => $request->network_id])->first();

        if (empty($coinNetwork))
            ResponseFacade::failed(__('Coin network not found'))->safeThrow();

        $coin = $coinNetwork->coin;
        if (empty($coin))
            ResponseFacade::failed(__('Coin not found'))->safeThrow();

        $network = $coinNetwork->network;
        if (empty($network))
            ResponseFacade::failed(__('Network not found'))->safeThrow();

        if (!NetworkBase::isCustomNetwork($network->base_type))
            ResponseFacade::failed(__('Network is invalid'))->safeThrow();

        $transactionDepositService = new TransactionDepositService();
        $transaction = $transactionDepositService->checkCoinTransactionAndDeposit($request, true);

        if ($request->type == CHECK_DEPOSIT) {
            $response = $transaction;
        } else {
            $response = $transactionDepositService->checkAddressAndDeposit($transaction['data']);
        }
        return $response;
    }
    // check bitgo transaction hash
    public function checkBitgoTransaction($network, $coin, $hash, $type)
    {
        try {
            $service = new WalletService();
            if (empty($coin->bitgo_wallet_id)) {
                $response = responseData(false, __('Bitgo wallet id is empty, please add it first'));
            } else {
                $checkHash = DepositeTransaction::where(['transaction_id' => $hash])->first();
                if (isset($checkHash)) {
                    $response = responseData(false, __('bitgoWalletCoinDeposit hash already in db'));
                } else {
                    $getTransaction = $service->getTransaction($coin->coin_type, $coin->bitgo_wallet_id, $hash);
                    if ($getTransaction['success'] == true) {
                        $bitgoService = new BitgoWalletService();
                        $transactionData = $getTransaction['data'];
                        if ($transactionData['type'] == 'receive' && $transactionData['state'] == 'confirmed') {
                            $coinVal = $bitgoService->getDepositDivisibilityValues($transactionData['coin']);
                            $amount = bcdivx($transactionData['value'], $coinVal, 8);

                            $data = [
                                'coin_type' => $transactionData['coin'],
                                'txId' => $transactionData['txid'],
                                'confirmations' => $transactionData['confirmations'],
                                'amount' => $amount,
                                'network_id' => $network->id,
                                'coin_id' => $coin->id
                            ];

                            if (isset($transactionData['entries'][0])) {
                                foreach ($transactionData['entries'] as $entry) {
                                    if (isset($entry['wallet']) && ($entry['wallet'] == $transactionData['wallet'])) {
                                        $data['address'] = $entry['address'];
                                        storeException('entry address', $data['address']);
                                    }
                                }
                            }
                            if (isset($data['address'])) {
                                if ($type == CHECK_DEPOSIT) {
                                    $response = ['success' => true, 'message' => __('Transaction found'), 'data' => $data];
                                } else {
                                    $response = $service->checkAddressAndDeposit($data);
                                }
                            } else {
                                $response = ['success' => false, 'message' => __('No address found')];
                            }
                        } else {
                            $response = ['success' => false, 'message' => __('The transaction type is not receive')];
                        }
                    } else {
                        $response = responseData(false, $getTransaction['message']);
                    }
                }
            }
        } catch (\Exception $e) {
            storeException('checkBitgoTransaction', $e->getMessage());
            $response = responseData(false, $e->getMessage());
        }
        return $response;
    }


    // check erc20 transaction hash
    public function checkERC20Transaction($coin, $hash, $type)
    {
        try {
            $service = new WalletService();
            if (empty($coin->chain_link)) {
                $response = responseData(false, __('Chain link is empty, please add it first'));
            } else {
                $checkHash = DepositeTransaction::where(['transaction_id' => $hash])->first();
                if (isset($checkHash)) {
                    $response = responseData(false, __('Transaction hash already in db'));
                } else {
                    $erc20Api = new ERC20TokenApi($coin);
                    $reqData = ['transaction_hash' => $hash, 'contract_address' => $coin->contract_address];
                    $getTransaction = $erc20Api->getTransactionData($reqData);
                    // dd($getTransaction);
                    if ($getTransaction['success'] == true) {
                        $transactionData = $getTransaction['data'];
                        $data = [
                            'coin_type' => $coin->coin_type,
                            'txId' => $transactionData->txID,
                            'confirmations' => 1,
                            'amount' => $transactionData->amount,
                            'address' => $transactionData->toAddress,
                            'from_address' => $transactionData->fromAddress
                        ];

                        if ($type == CHECK_DEPOSIT) {
                            $response = ['success' => true, 'message' => __('Transaction found'), 'data' => $data];
                        } else {
                            $response = $service->checkAddressAndDeposit($data);
                        }
                    } else {
                        $response = responseData(false, $getTransaction['message']);
                    }
                }
            }
        } catch (\Exception $e) {
            storeException('checkBitgoTransaction', $e->getMessage());
            $response = responseData(false, $e->getMessage());
        }
        return $response;
    }
    public function checkTRC20Transaction($coin, $hash, $type)
    {
        try {
            $service = new WalletService();
            if (empty($coin->chain_link)) {
                $response = responseData(false, __('Chain link is empty, please add it first'));
            } else {
                $checkHash = DepositeTransaction::where(['transaction_id' => $hash])->first();
                if (isset($checkHash)) {
                    $response = responseData(false, __('Transaction hash already in db'));
                } else {
                    $erc20Api = new ERC20TokenApi($coin);
                    $reqData = ['transaction_hash' => $hash, 'contract_address' => $coin->contract_address];
                    $getTransaction = $erc20Api->getTrxTransaction($reqData);
                    if ($getTransaction['success'] == true) {
                        $transactionData = $getTransaction['data'];
                        $data = [
                            'coin_type' => $coin->coin_type,
                            'txId' => $transactionData->transaction,
                            'confirmations' => 1,
                            'amount' => ($transactionData->result->value / 1000000),
                            'address' => $transactionData->result->to,
                            'from_address' => $transactionData->result->from
                        ];

                        if ($type == CHECK_DEPOSIT) {
                            $response = ['success' => true, 'message' => __('Transaction found'), 'data' => $data];
                        } else {
                            $response = $service->checkAddressAndDeposit($data);
                        }
                    } else {
                        $response = responseData(false, $getTransaction['message']);
                    }
                }
            }
        } catch (\Exception $e) {
            storeException('checkTRC20Transaction', $e->getMessage());
            $response = responseData(false, $e->getMessage());
        }
        return $response;
    }
    public function getTransactionInformation($network, $coin_network, $coin, $hash, $type)
    {
        try {
            $service = new WalletService();
            if (empty($network->rpc_url)) {
                $response = responseData(false, __('Chain link is empty, please add it first'));
            } else {
                $checkHash = DepositeTransaction::where(['transaction_id' => $hash])->first();
                if (isset($checkHash)) {
                    $response = responseData(false, __('Transaction hash already in db'));
                } else {
                    $getTransaction = (new EvmWalletService)->checkDepositByTx([
                        'network' => $network->id,
                        'coin_network' => $coin_network->id,
                        'transaction_id' => $hash
                    ]);
                    if (isset($getTransaction['success']) && $getTransaction['success']) {
                        $transactionData = (object) $getTransaction['data'];
                        $data = [
                            'coin_type' => $coin->coin_type,
                            'txId' => $transactionData->hash,
                            'confirmations' => 1,
                            'amount' => $transactionData->amount,
                            'address' => $transactionData->address,
                            'from_address' => $transactionData->from,
                            'network_id' => $network->id,
                            'coin_id' => $coin->id
                        ];

                        if ($type == CHECK_DEPOSIT) {
                            $response = ['success' => true, 'message' => __('Transaction found'), 'data' => $data];
                        } else {
                            $response = $service->checkAddressAndDeposit($data);
                        }
                    } else {
                        $response = responseData(false, $getTransaction['message']);
                    }
                }
            }
        } catch (\Exception $e) {
            storeException('checkTRC20Transaction', $e->getMessage());
            $response = responseData(false, $e->getMessage());
        }
        return $response;
    }

    public function transferPendingDepositUserToSystemWallet(string $id)
    {
        $transaction_id = decrypt($id);

        $transaction = DepositeTransaction::with('networkInfo')->where([
            'id' => $transaction_id,
            'is_admin_receive' => DepositCollectionStatus::PENDING->value,
        ])->first();

        if (empty($transaction))
            ResponseFacade::failed(__('Pending deposit not found'))->safeThrow();

        // accept evm base coin deposit from admin panel
        $network = $transaction->networkInfo;
        if (empty($network))
            ResponseFacade::failed(__('Network not found'))->safeThrow();

        $isEvmBaseCoin = NetworkBase::isCustomNetwork($network->base_type);
        if (!$isEvmBaseCoin)
            ResponseFacade::failed(__('Only EVM base coin deposit is possible here'))->safeThrow();

        PendingDepositAcceptJob::dispatch($transaction);

        return success(__('Pending deposit accept process goes to queue. Please wait sometimes'));
    }
}
