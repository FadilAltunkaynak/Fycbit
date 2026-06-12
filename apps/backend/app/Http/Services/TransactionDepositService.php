<?php

namespace App\Http\Services;

use App\Enums\DepositeStatus;
use App\Facades\ResponseFacade;
use App\Http\Resources\DepositTransactionResource;
use Exception;
use App\Model\Coin;
use App\Model\Wallet;
use App\Model\Network;
use App\Model\CoinNetwork;
use App\Jobs\TransactionDeposit;
use App\Model\DepositeTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Model\WalletAddressHistory;
use App\Exceptions\InvalidRequestException;
use App\Http\Services\Evm\EvmWalletService;
use App\Traits\NumberFormatTrait;
use App\Traits\ResponseFormatTrait;

class TransactionDepositService
{
    use ResponseFormatTrait, NumberFormatTrait;

    public function __construct()
    {
    }

    public function getNetworks(): array
    {
        $networks = Network::whereNotIn("base_type", [1, 2, 3])->whereStatus(STATUS_ACTIVE)->get(["id", "name"]);
        return responseData(true, __("Networks get successfully"), $networks);
    }

    public function getCoinNetwork($request): array
    {
        if (!isset($request->network_id))
            return responseData(false, __("Network is required"));
        if (!is_numeric($request->network_id))
            return responseData(false, __("Network is invalid"));

        $responseData = [];
        $coin_network = CoinNetwork::with("coin:id,name,coin_type")->where("network_id", $request->network_id)->get();

        $coin_network->map(function ($coinNetwork) use (&$responseData) {
            $new = new \stdClass;
            $new->id = $coinNetwork->coin?->id;
            $new->name = $coinNetwork->coin?->name;
            $new->coin_type = $coinNetwork->coin?->coin_type;
            $new->network_id = $coinNetwork?->network_id;
            $responseData[] = $new;
        });

        return responseData(true, __("Network coins get successfully"), $responseData);
    }

    /**
     * Summary of checkCoinTransactionAndDeposit
     * @param Request $request
     * @param bool $isAdmin
     * @return array
     */
    public function checkCoinTransactionAndDeposit(Request $request, bool $isAdmin = false): array
    {
        $coin = CoinNetwork::with('network')->where(
            ["currency_id" => $request->coin_id, "network_id" => $request->network_id]
        )->first();

        if (empty($coin))
            ResponseFacade::failed(__("Coin not found"))->safeThrow();

        $deposit = DepositeTransaction::where("transaction_id", $request->trx_id)->first();

        if ($deposit)
            return success(
                __("This transaction already deposited in our system"),
                (new DepositTransactionResource($deposit))->toArray(request())
            );

        $evmService = new EvmWalletService();
        $getTransaction = $evmService->checkDepositByTx([
            'network' => $coin->network_id,
            'coin_network' => $coin->id,
            'transaction_id' => $request->trx_id
        ]);

        if (!is_success($getTransaction))
            ResponseFacade::failed($getTransaction["message"])->safeThrow();

        $transactionData = $getTransaction['data'];
        $data = [
            'coin_type' => $transactionData["coin_type"],
            'txId' => $transactionData["hash"],
            'confirmations' => STATUS_ACTIVE,
            'amount' => truncate_num($transactionData["amount"]),
            'from_address' => $transactionData["fromAddress"],
            'address' => $transactionData["toAddress"],
            'block_number' => @$transactionData["block_number"] ?? null,
            'coin_id' => $coin->currency_id,
            'network_id' => $coin->network_id,
            'network_type' => $coin->network->base_type,
        ];

        $responseData = $data;
        $responseData["network"] = $coin->network->name;

        $checkAddress = WalletAddressHistory::where(['address' => $data['address'], 'coin_type' => $data['coin_type']])->first();
        if (empty($checkAddress))
            return success(__("Transaction details found but To address not match in system"), $responseData);

        $wallet = Wallet::find($checkAddress->wallet_id);
        if (empty($wallet))
            return success(__("This transaction already deposited in our system"), $responseData);

        if (!$isAdmin)
            TransactionDeposit::dispatch($data)->onQueue("deposit");

        return success(__("Transaction details found, System will adjust deposit soon"), $responseData);
    }

    /**
     * Summary of checkAddressAndDeposit
     * @param mixed $data
     * @return array
     */
    public function checkAddressAndDeposit($data)
    {
        $checkDeposit = DepositeTransaction::where(['transaction_id' => $data['txId']])->first();
        if ($checkDeposit)
            ResponseFacade::failed(__("Transaction already exist"))->safeThrow();

        if (isset($data['network_id'])) {
            $checkAddress = WalletAddressHistory::where([
                'address' => $data['address'],
                'coin_type' => $data['coin_type'],
                'network_id' => $data['network_id'],
                'coin_id' => $data['coin_id']
            ])->first();
        } else {
            $checkAddress = WalletAddressHistory::where(['address' => $data['address'], 'coin_type' => $data['coin_type']])->first();
        }

        if (empty($checkAddress))
            ResponseFacade::failed(
                __('This address not found in db the address is :address', ['address' => $data['address']])
            )->safeThrow();

        $wallet = Wallet::find($checkAddress->wallet_id);
        if (empty($wallet))
            ResponseFacade::failed('Wallet not found')->safeThrow();

        DB::beginTransaction();
        try {
            DepositeTransaction::create($this->depositData($data, $wallet));
            $wallet->increment('balance', $data['amount']);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            ResponseFacade::failed($e->getMessage())->safeThrow();
        }
        return success(__('Wallet deposited successfully'));
    }

    // deposit data
    public function depositData($data, $wallet)
    {
        return [
            'address' => $data['address'],
            'from_address' => $data['from_address'] ?? "",
            'receiver_wallet_id' => $wallet->id,
            'address_type' => ADDRESS_TYPE_EXTERNAL,
            'coin_type' => $wallet->coin_type,
            'amount' => $data['amount'],
            'transaction_id' => $data['txId'],
            'status' => DepositeStatus::SUCCESS->value,
            'confirmations' => $data['confirmations'],
            'coin_id' => $wallet->coin_id,
            'network_id' => $data['network_id'],
            'network_type' => $data['network_type'],
            'block_number' => $data["block_number"],
        ];
    }
}
