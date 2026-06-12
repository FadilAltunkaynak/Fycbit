<?php

namespace App\Http\Services\Evm;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class EvmWalletService
{
    private static $host;
    private static $api_secret;

    public function __construct()
    {
        self::$host = env('EVM_APP_HOST');
        self::$api_secret = env('EVM_APP_SECRET');
    }

    public function checkSystemWalletAddress(int $network_id, string $pk)
    {
        return $this->__send('/evm/check-address', ['network' => $network_id, 'private_key' => $pk], 'post');
    }

    public function createSystemWallet(int $network_id)
    {
        return $this->__send('/evm/create-system-wallet', ['network' => $network_id], 'post');
    }

    public function createWallet(array $request_data)
    {
        return $this->__send('/evm/create-wallet', $request_data, 'post');
    }
    public function createWalletAddress(array $request_data)
    {
        return $this->__send('/evm/create-wallet-address', $request_data, 'POST');
    }

    public function callDepositCommand()
    {
        return $this->__send('/evm/check-deposit', 'get');
    }

    public function callBlockDepositCommand()
    {
        return $this->__send('/evm/block-deposit-check', 'get');
    }

    public function withdrawalExternalApproval(array $request_data)
    {
        return $this->__send('/evm/withdrawal-external-approval', $request_data, 'post');
    }

    public function acceptDepositFromUser(int $transaction_id)
    {
        return $this->__send('/evm/receive-deposit-coin', ['transaction_id' => $transaction_id], 'post');
    }

    public function checkDepositByTx(array $request_data)
    {
        return $this->__send('/evm/check-deposit-coin', $request_data, 'post');
    }

    public function checkCurrentBlockNumber(array $request_data)
    {
        return $this->__send('/evm/current-block', $request_data, 'post');
    }

    public function sendEvmToken(array $request_data)
    {
        return $this->__send('/evm/send-token', $request_data, 'post');
    }

    public function checkContractAddress(array $request_data)
    {
        return $this->__send('/evm/check-contract-address', $request_data, 'post');
    }

    public function startBlockProcessor(int $networkId)
    {
        return $this->__send('/evm/start-single-network-block-processor', ['network_id' => $networkId], 'post');
    }

    public function validateContractAddress(int $baseType, string $contractAddress, string $rpc)
    {
        return $this->__send('/evm/check-contract-address-validity', [
            'contract_address' => $contractAddress,
            'rpc_url' => $rpc,
            'base_type' => $baseType
        ], 'post');
    }

    private function __send($url, $body = [], $method = 'GET')
    {
        $client = new Client();
        $reqUrl = concatBaseUrlAndEndpoint(self::$host, $url);

        $options = [
            'headers' => [
                "Accept" => "application/json",
                "evmapisecret" => self::$api_secret,
                "token" => session()->get('evm_token'),
            ],
        ];
        if ($method != 'GET') {
            $options['form_params'] = $body; // Use form_params for POST requests
        }
        try {
            $response = $client->request(strtoupper($method), $reqUrl, $options);
            $res = $response->getBody()->getContents();

            return json_decode($res, 1);
        } catch (RequestException $e) {
            storeLog(processExceptionMsg($e), 'error');
            return failed($e->getMessage());
        }
    }
}
