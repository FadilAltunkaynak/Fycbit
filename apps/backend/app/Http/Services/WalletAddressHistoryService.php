<?php

namespace App\Http\Services;

use App\Enums\CoinProvider;
use App\Enums\NetworkBase;
use App\Enums\UsdtCoinPaymentNetwork;
use App\Enums\WalletAddressStatus;
use App\Facades\ResponseFacade;
use App\Http\Repositories\CoinSettingRepository;
use App\Http\Services\Evm\EvmWalletService;
use App\Model\Coin;
use App\Model\Network;
use Exception;
use DateTimeImmutable;
use App\Model\WalletAddressHistory;
use App\Model\WalletNetwork;
use App\Traits\ResponseFormatTrait;
use App\Enums\CoinPaymentActiveVersion;

class WalletAddressHistoryService
{
    use ResponseFormatTrait;

    public $repository;
    public $bitgoService;

    public function __construct()
    {
        // $this->repository = new WalletRepository();
        // $this->bitgoService = new BitgoWalletService();
    }

    /**
     * Summary of getUserWalletAddress
     * @param int $userId
     * @param Coin $coin
     * @param Network $network
     * @param int $walletId
     * @return array
     */
    public function getUserWalletAddress(int $userId, Coin $coin, Network $network, int $walletId): array
    {
        $walletAddressDB = WalletAddressHistory::where([
            'user_id' => $userId,
            'network_id' => $network->id,
            'coin_id' => $coin->id
        ])->where('status', '!=', WalletAddressStatus::EXPIRE->value)->first();

        if (empty($walletAddressDB->address)) {
            $addressResponse = $this->generateWalletAddress($coin, $network)['data'];

            $wallet_key = isset($addressResponse['wallet_key']) ? custom_encrypt($addressResponse['wallet_key']) : null;

            $walletAddress = $this->getWalletAddressHistory(
                $userId,
                $coin,
                $network->id,
                $walletId,
                $addressResponse['address'],
                $wallet_key,
                $addressResponse['public_key'],
                $addressResponse['memo'],
                $addressResponse['wallet_id'],
                $addressResponse['rented_till']
            )['data'];
        } else
            $walletAddress = $this->getWalletAddressHistory(
                $userId,
                $coin,
                $network->id,
                $walletId,
                $walletAddressDB->address,
                $walletAddressDB->wallet_key,
                $walletAddressDB->public_key,
                $walletAddressDB->memo,
            )['data'];

        return success($walletAddress);
    }

    /**
     * Summary of generateWalletAddress
     * @param Coin $coin
     * @param Network $network
     * @return array
     */
    public function generateWalletAddress(Coin $coin, Network $network): array
    {
        $address = '';
        $data = [
            'wallet_key' => '',
            'public_key' => '',
            'memo' => '',
            "wallet_id" => null,
            "rented_till" => null,
        ];
        if (NetworkBase::isCoinPayment($network->base_type)) {
            $addressInfo = $this->getCoinPaymentWalletAddress($coin->coin_type);
            if (is_success($addressInfo)) {
                $addressInfo = $addressInfo['data'];
                $address = $addressInfo['address'];
                $data['memo'] = $addressInfo['memo'];
                $data['wallet_id'] = $addressInfo['wallet_id'];
                $data['rented_till'] = $addressInfo['rented_till'];
            }
        } elseif (NetworkBase::isBitgo($network->base_type)){
            $result = $this->getBitGoWalletAddress($coin->coin_type)['data'];
            if ($result['address'] ?? '') {
                $address = $result['address'];
            }
        }elseif (NetworkBase::isBitcoin($network->base_type)){
            $result = $this->getBitCoinWalletAddress($coin->coin_type)['data'];
            if ($result['address'] ?? '') {
                $address = $result['address'];
            }
        }elseif (NetworkBase::isCustomNetwork($network->base_type)) {
            $result = $this->getCustomRpcWalletAddress($network)['data'];
            if ($result) {
                $address = $result['address'];
                $data['wallet_key'] = $result['pk'];
            }
        }
        $data['address'] = $address;

        if (empty($address))
            ResponseFacade::failed(__('Failed to generate wallet address'))->safeThrow();

        return success($data);
    }

    /**
     * Summary of coinPaymentNetworks
     * @param string $coin_type
     * @param int $activeProvider
     * @return array
     */
    public function coinPaymentNetworks(string $coin_type, int $activeProvider): array
    {
        if ($coin_type != COIN_USDT && CoinProvider::isCoinPayment($activeProvider))
            ResponseFacade::failed(__("Only USDT wallet networks are supported for this operation"))->safeThrow();

        $networks = [];
        foreach (UsdtCoinPaymentNetwork::getList() as $key => $val) {
            array_push($networks, [
                'network_type' => $key,
                'network_name' => $val,
            ]);
        }
        return $this->responseData(true, __('success'), $networks);
    }

    /**
     * Get CoinPayment Wallet Address
     * @param string $payment_type
     * @return array{data: mixed, message: string, success: bool}
     */
    public function getCoinPaymentWalletAddress(string $payment_type): array
    {
        $ipnUrl = url('api/coin-payment-notifier');
        $coinPaymentVersion = CoinPaymentActiveVersion::tryFrom(settings('COIN_PAYMENT_VERSION') ?? 0);
        if(!$coinPaymentVersion) {
            storeException('Coin payment version invalid ', 'on deposit adderss generate');
            return failed(__("Failed"));
        }

        $coinPaymentService = $coinPaymentVersion->getService();
        $address = match($coinPaymentVersion){
            CoinPaymentActiveVersion::LEGACY => $coinPaymentService->GetCallbackAddress($payment_type, $ipnUrl),
            CoinPaymentActiveVersion::COIN_PAYMENT_V2 => $coinPaymentService->getWalletAddress($payment_type)
        };

        if (isset($address['error']) && ($address['error'] != 'ok')){
            storeException('coinPayment address generation error', $address['error'] ?? 'CoinPayment address generation failed');
            return failed($address['error'] ?? __("CoinPayment address generation failed"));
        }

        $data['memo'] = "";
        $data['address'] = $address['result']['address'];
        $data['wallet_id'] = $address['result']['wallet_id'] ?? null;
        $data['rented_till'] = $address['result']['rented_till'] ?? null;

        if (isset($address['result']['dest_tag']))
            $data['memo'] = $address['result']['dest_tag'];

        return success($data);
    }

    /**
     * Summary of getBitCoinWalletAddress
     * @param string $coin_type
     * @return array
     */
    public function getBitCoinWalletAddress(string $coin_type)
    {
        $coin = (new CoinSettingRepository())->getCoinSettingData($coin_type, BITCOIN_API);
        if (empty($coin))
            ResponseFacade::failed(__('Coin not found'))->safeThrow();

        $bitCoinApi = new BitCoinApiService($coin->coin_api_user, decryptId($coin->coin_api_pass), $coin->coin_api_host, $coin->coin_api_port);
        $data['address'] = $bitCoinApi->getNewAddress();

        if (empty($data['address']))
            throw new Exception(__('Failed to bitcoin address generate'));

        return success($data);
    }

    /**
     * Summary of getBitGoWalletAddress
     * @param string $coin_type
     * @return array
     */
    public function getBitGoWalletAddress(string $coin_type): array
    {
        $coin = (new CoinSettingRepository())->getCoinSettingData($coin_type, BITGO_API);
        if (empty($coin))
            ResponseFacade::failed(__('Coin not found'))->safeThrow();

        if (empty($coin->bitgo_wallet_id))
            ResponseFacade::failed(__('Bitgo wallet not found'))->safeThrow();

        $bitgoApi = new BitgoWalletService();
        $address = $bitgoApi->createBitgoWalletAddress($coin->coin_type, $coin->bitgo_wallet_id, $coin->chain);

        if (!is_success($address))
            ResponseFacade::failed(__('Failed to generate bitgo address'))->safeThrow();

        $data['address'] = $address['data']['address'];

        return success($data);
    }

    /**
     * Summary of getCustomRpcWalletAddress
     * @param Network $network
     * @return array
     */
    public function getCustomRpcWalletAddress(Network $network): array
    {
        $reqBody = ['network' => $network->id];
        $response = (new EvmWalletService())->createWalletAddress($reqBody);

        if (!is_success($response))
            ResponseFacade::failed($response['message'])->safeThrow();

        return $response;
    }

    /**
     * Summary of getWalletAddressHistory
     * @param int $user_id
     * @param Coin $coin
     * @param int $network_id
     * @param int $wallet_id
     * @param string $address
     * @param string|null $wallet_key
     * @param string|null $public_key
     * @param string|null $memo
     * @param string|int|null $c_wallet_id = null,
     * @param ?DateTimeImmutable $rented_till = null
     * @return array
     */
    public function getWalletAddressHistory(int $user_id, Coin $coin, int $network_id, int $wallet_id, string $address, ?string $wallet_key = null, ?string $public_key = null, ?string $memo = null, string|int|null $c_wallet_id = null, ?DateTimeImmutable $rented_till = null): array
    {
        $walletAddress = WalletAddressHistory::updateOrCreate(
            [
                'user_id' => $user_id,
                'network_id' => $network_id,
                'wallet_id' => $wallet_id,
                'coin_type' => $coin->coin_type,
            ],
            [
                'coin_id' => $coin->id,
                'address' => $address,
                'wallet_key' => $wallet_key,
                'public_key' => $public_key,
                'is_encrypted' => 1,
                'memo' => $memo,
                "coin_payment_wallet_id" => $c_wallet_id,
                'rented_till' => $rented_till,
                'status' => WalletAddressStatus::ACTIVE->value
            ]
        );

        return success($walletAddress);
    }

    /**
     * Summary of isInternalAddress
     * @param string $address
     * @param string $coinType
     * @return array
     */
    public function isInternalAddress(string $address, string $coinType): array
    {
        $checkAddress = WalletAddressHistory::with('wallet')->where(['address' => $address, "coin_type" => $coinType])->first();
        if (!$checkAddress)
            $checkAddress = WalletNetwork::with('wallet')->where('address', $address)->first();

        return success($checkAddress);
    }
}
