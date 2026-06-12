<?php

namespace App\Http\Services;

use Exception;
use App\Model\Coin;
use App\Model\Network;
use App\Enums\CoinType;
use App\Model\CoinNetwork;
use App\Enums\CoinProvider;
use App\Model\NotifiedBlock;
use Illuminate\Http\Request;
use App\Facades\ResponseFacade;
use App\Model\SupportedNetwork;
use Illuminate\Support\Facades\DB;
use App\Exceptions\RpcNotSetException;
use App\Http\Services\Evm\EvmWalletService;

class NetworkService
{
    public function __construct()
    {
    }

    public function createNetworkProcess($request)
    {
        try {
            $finder = [
                'id' => $request->id ?? 0
            ];
            $network = new Network;
            $insertData = [
                "name" => $request->name,
                "description" => $request->description ?? "",
                "status" => isset($request->status)
            ];
            DB::beginTransaction();
            if (!isset($request->id) && isset($request->slug)) {
                if ($network = SupportedNetwork::whereSlug($request->slug)->first()) {
                    $insertData["slug"] = $network->slug;
                    $insertData["base_type"] = $network->type;
                    $insertData["block_confirmation"] = $request->block_confirmation;
                    $insertData["rpc_url"] = $request->rpc_url;
                    $insertData["wss_url"] = $request->wss_url ?? '';
                    $insertData["explorer_url"] = $request->explorer_url;
                    $insertData["chain_id"] = $network->chain_id;
                } else {
                    DB::rollBack();
                    return responseData(false, __('Supported network not found'));
                }
            } else {
                if ($network = Network::find($request->id)) {
                    if (isset($request->block_number)) {
                        if ($block = NotifiedBlock::where("network_id", $network->id)->first()) {
                            $block->update(["block_number" => is_numeric($request->block_number) ? $request->block_number : 0]);
                        }
                    }
                    $insertData["block_confirmation"] = $request->block_confirmation;
                    $insertData["rpc_url"] = $request->rpc_url;
                    $insertData["wss_url"] = $request->wss_url ?? '';
                    $insertData["explorer_url"] = $request->explorer_url;
                    $insertData["from_block_number"] = $request->from_block_number ? $request->from_block_number : 0;
                    $insertData["to_block_number"] = $request->to_block_number ? $request->to_block_number : 0;
                } else {
                    DB::rollBack();
                    return responseData(false, __("Network not found"));
                }
            }

            if ($request->hasFile('logo')) {
                if (isset($request->id)) {
                    deleteFile(IMG_NETWORK_LOGO_PATH, $network->logo);
                    if ($logo = uploadFile($request->file('logo'), IMG_NETWORK_LOGO_PATH))
                        $insertData['logo'] = $logo;
                } else if ($logo = uploadFile($request->file('logo'), IMG_NETWORK_LOGO_PATH)) {
                    $insertData['logo'] = $logo;
                }
            }
            $network = Network::updateOrCreate($finder, $insertData);
            if ($network) {
                if (isset($request->id)) {
                    DB::commit();
                    return responseData(true, __("Network updated successfully"));
                } else {
                    NotifiedBlock::create(['network_id' => $network->id]);
                    DB::commit();
                    return responseData(true, __("Network created successfully"));
                }
            }
            if (isset($request->id))
                return responseData(true, __("Network failed to update"));
            return responseData(false, __("Network failed to create"));
        } catch (\Exception $e) {
            DB::rollBack();
            storeException("createNetworkProcess", $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function createCoinNetworkProcess($request)
    {
        $coin = Coin::find($request->currency_id);
        if (!$coin)
            return failed(__("Coin not found"));

        $network = Network::find($request->network_id);
        if (!$coin)
            return failed(__("Network not found"));

        if (CoinProvider::isCustomRpc($network->provider_type)) {
            if (!isset($request->coin_decimal))
                return failed(__("Please add coin decimal"));
        }

        $checkAlreadyExists = CoinNetwork::where(["network_id" => $request->network_id, "currency_id" => $request->currency_id])->exists();

        if ($checkAlreadyExists)
            return failed(__("Already exists same coin network"));

        if (CoinType::isNative($request->type)) {
            $supportedNetwork = SupportedNetwork::where('slug', $network->slug)->first();
            if (empty($supportedNetwork))
                return failed(false, __("Supported network not found"));

            $isCustomRPC = in_array($supportedNetwork->type, CoinProvider::customRpcGroup());
            if ($supportedNetwork->native_currency !== $coin->coin_type && $isCustomRPC) {
                return failed(__(":network network native currency must be :coin", [
                    "network" => $network->name,
                    "coin" => $supportedNetwork->native_currency,
                ]));
            }
        }
        $insertData = [
            "uid" => uniqid() . time(),
            "network_id" => $request->network_id,
            "currency_id" => $request->currency_id,
            "type" => $request->type,
            "contract_address" => $request->contract_address ?? '',
            "withdrawal_fees" => $request->withdrawal_fees ?? 0,
            "withdrawal_fees_type" => $request->withdrawal_fees_type ?? SEND_FEES_FIXED,
            "coin_decimal" => $request?->coin_decimal,
            "status" => isset($request->status),
        ];
        CoinNetwork::create($insertData);
        return responseData(true, __("Coin network created successfully"));
    }

    public function updateCoinNetworkProcess($request)
    {
        $coin = Coin::find($request->currency_id);
        if (!$coin)
            return failed(__("Coin not found"));

        $network = Network::find($request->network_id);
        if (!$coin)
            return failed(__("Network not found"));

        if (CoinProvider::isCustomRpc($network->provider_type)) {
            if (!isset($request->coin_decimal))
                return failed(__("Please add coin decimal"));
        }

        $coinNetwork = CoinNetwork::where([
            "uid" => $request->uid,
            "network_id" => $request->network_id,
            "currency_id" => $request->currency_id
        ])->first();

        if (!$coinNetwork)
            return failed(__("Coin network not found"));

        if (
            $request->type == TOKEN_COIN 
            && $request->contract_address !== $coinNetwork->contract_address
        ) {
            try {
                $service = app(ContractAddressVerificationService::class);
                $isAllowed = $service->verifyContractAddress(
                    $request->base_type,
                    $request->contract_address,
                    $network->rpc_url
                );

                if (!$isAllowed->success) {
                    return responseData(false, $isAllowed->message);
                }
            } catch (RpcNotSetException $e) {
                return failed('Rpc Not Set For ' . $network->name);
            } catch (Exception $e) {
                storeException('CoinNetworkProxyService', $e->getMessage());
                return failed( "Something went wrong");
            }
        }

        $updateData = [
            "network_id" => $request->network_id,
            "currency_id" => $request->currency_id,
            "type" => $request->type,
            "contract_address" => $request?->contract_address,
            "withdrawal_fees" => $request->withdrawal_fees ?? 0,
            "withdrawal_fees_type" => $request->withdrawal_fees_type ?? SEND_FEES_FIXED,
            "coin_decimal" => $request?->coin_decimal,
            "status" => isset($request->status),
        ];
        $coinNetwork->update($updateData);
        return success(__("Coin network updated successfully"));
    }

    public function deleteNetwork(string $id): array
    {
        try {
            if ($network = Network::find($id)) {
                if ($coin_network = CoinNetwork::where('network_id', $id)->first())
                    return responseData(false, __("This network has been merged with coins in coin network, So delete action aborted"));

                $network->delete();
                return responseData(true, __("Network deleted successfully"));
            }
            return responseData(false, __("Network not found"));
        } catch (\Exception $e) {
            storeException("deleteNetwork", $e->getMessage());
            return responseData(false, __("Somthing went worng"));
        }
    }

    public function deleteCoinNetwork(int $id): array
    {
        try {
            if ($coin_network = CoinNetwork::find($id)) {
                $coin_network->delete();
                return responseData(true, __("Coin network deleted successfully"));
            }
            return responseData(false, __("Coin network not found"));
        } catch (\Exception $e) {
            storeException('deleteCoinNetwork', $e->getMessage());
            return responseData(false, __("Somthing went worng"));
        }
    }

    public function coinNetworkDelete(string $id): array
    {
        $id = decryptId($id);
        $coinNetwork = CoinNetwork::with('coin')->find($id);
        if ($coinNetwork)
            return failed(__("Coin network not found"));

        if ($coinNetwork->coin)
            return failed(false, __("Coin not found"));

        $checkConditions = (new CoinService)->checkCoinDeleteCondition($coinNetwork->coin);

        if (!is_success($checkConditions))
            return failed($checkConditions['message']);

        $coinNetwork->delete();
        return success(__("Coin network deleted successfully"));
    }

    public function checkLatestBlock($request): array
    {
        try {
            $service = new EvmWalletService();
            if ($network = Network::find($request->id)) {
                if ($network->rpc_url) {
                    $response = $service->checkCurrentBlockNumber(['id' => $request->id]);
                    return $response;
                } else {
                    return responseData(false, __("Please add the rpc url first"));
                }
                return responseData(true, __("Network deleted successfully"));
            }
            return responseData(false, __("Network not found"));
        } catch (\Exception $e) {
            storeException("deleteNetwork", $e->getMessage());
            return responseData(false, __("Somthing went worng"));
        }
    }

    public function networksByCoinProvider($coin_id): array
    {
        $coin = Coin::find($coin_id);
        if (!$coin)
            return failed(__("Coin not found"));

        if (empty($coin->active_provider))
            return failed(__("Please set active coin provider from coin edit"));

        $baseTypes = array_keys(CoinProvider::tryFrom($coin->active_provider)?->getBaseTypes() ?? []);
        $networks = Network::whereIn('base_type', $baseTypes)
            ->where('status', STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'provider_type', 'base_type']);

        return $networks ? success($networks) : failed("Network list failed to get");
    }
}
