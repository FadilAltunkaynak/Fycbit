<?php

namespace App\Http\Services;

use App\Exceptions\RpcNotSetException;
use App\Http\Requests\Admin\CreateCoinNetworkRequest;
use App\Model\CoinNetwork;
use App\Model\Network;
use Exception;

class CoinNetworkProxyService
{
    public function __construct(
        private NetworkService $networkService,
        private ContractAddressVerificationService $contractAddressVerificationService
    ) {
    }

    ///@todo refactor this
    public function createCoinNetwork(CreateCoinNetworkRequest $request): array
    {
        $shouldProcess = true;
        $networkId = $request->network_id;
        if (isset($request->uid)) {
            $coinNetwork = CoinNetwork::where('uid', $request->uid)->first();
            $networkId = $coinNetwork->network_id;
            if ($request->contract_address == $coinNetwork->contract_address) {
                $shouldProcess = false;
            }
        }

        if ($request->type == TOKEN_COIN && $shouldProcess) {
            $networkData = Network::find($networkId);

            try {
                $isAllowed = $this->contractAddressVerificationService->verifyContractAddress(
                    $request->base_type,
                    $request->contract_address,
                    $networkData->rpc_url
                );

                if (!$isAllowed->success) {
                    return responseData(false, $isAllowed->message);
                }
            } catch (RpcNotSetException $e) {
                return responseData(false, 'Rpc Not Set For ' . $networkData->name);
            } catch (Exception $e) {
                storeException('CoinNetworkProxyService', $e->getMessage());
                return responseData(false, "Something went wrong");
            }
        }

        return $this->networkService->createCoinNetworkProcess($request);
    }
}
