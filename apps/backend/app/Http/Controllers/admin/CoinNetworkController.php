<?php

namespace App\Http\Controllers\admin;

use App\Enums\CoinProvider;
use App\Enums\CoinType;
use App\Enums\NetworkBase;
use App\Facades\ResponseFacade;
use App\Model\Coin;
use App\Model\Network;
use App\Model\CoinNetwork;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Services\NetworkService;
use App\Http\Services\CoinSettingService;
use App\Http\Requests\Admin\CreateCoinNetworkRequest;
use App\Http\Services\CoinNetworkProxyService;
use App\Http\Services\DataTable\CoinNetworkDataTableService;
use App\Model\CoinSetting;

class CoinNetworkController extends Controller
{
    public function __construct(
        private NetworkService $service,
        private CoinSettingService $coinSettingService,
        private CoinNetworkProxyService $coinNetworkProxyService
    ) {
    }

    public function getCoinNetworkList(Request $request)
    {
        if ($request->ajax())
            return (new CoinNetworkDataTableService)->getData();

        $data['title'] = __("Coin With Network");
        return view("admin.coin_management.coin_network.coin_network", $data);
    }

    public function createCoinNetwork()
    {
        $data = [
            'title' => __('Create Coin Network'),
            'currencies' => Coin::where('currency_type', CURRENCY_TYPE_CRYPTO)
                ->where('status', STATUS_ACTIVE)
                ->orderBy('coin_type')->get(['id', 'coin_type']),
            'coinTypes' => CoinType::getList()
        ];
        return ResponseFacade::success($data)
            ->next_view('admin.coin_management.coin_network.coin_network_create')->send();
    }

    public function updateCoinNetwork($id = 0)
    {
        $id = decryptId($id);

        if (isset($id['success']))
            return ResponseFacade::failed(__('Invalid parameter'))->send();

        $coinNetwork = CoinNetwork::with('coin', 'network')->find($id);
        if (empty($coinNetwork))
            return ResponseFacade::failed(__('Coin network not found'))->send();

        $data = [
            'title' => __('Update Coin Network'),
            'item' => $coinNetwork,
            'currencies' => Coin::whereId($coinNetwork->currency_id)->get(['id', 'coin_type']),
            'networks' => Network::whereId($coinNetwork->network_id)->get(['id', 'name']),
            'isExternalExchange' => in_array($coinNetwork->network->provider_type, CoinProvider::externalGroup()),
            'coinTypes' => CoinType::getList()
        ];
        return ResponseFacade::success($data)
            ->next_view('admin.coin_management.coin_network.coin_network_edit')->send();
    }

    ///@todo Make update separate function
    public function createCoinNetworkProcess(CreateCoinNetworkRequest $request)
    {
        $response = $this->coinNetworkProxyService->createCoinNetwork($request);
        return ResponseFacade::result($response)->redirect_next('getCoinNetworkList')->send();
    }

    public function updateCoinNetworkProcess(CreateCoinNetworkRequest $request)
    {
        $response = $this->service->updateCoinNetworkProcess($request);
        return ResponseFacade::result($response)->send();
    }

    public function changeCoinNetworkStatus(Request $request)
    {
        if (isset($request->id)) {
            if ($network = CoinNetwork::find($request->id)) {
                $network->status = !$network->status;
                if ($network->save())
                    return responseData(true, __("Coin network status changed successfully"));
                return responseData(false, __("Coin network failed to changed status"));
            }
            return responseData(false, __("Coin network not found"));
        }
        return responseData(false, __("Coin network id is missing"));
    }

    // edit coin settings
    public function coinNetworkSettings($id)
    {
        $networkId = decryptId($id);
        if (isset($id['success']))
            return ResponseFacade::failed(__('Invalid parameter'))->send();

        $coinNetwork = CoinNetwork::with(['coin', 'network'])->where('id', $networkId)->first();
        if (empty($coinNetwork))
            return ResponseFacade::failed(__('Coin network not found'))->send();

        $coin = $coinNetwork?->coin;
        $network = $coinNetwork?->network;

        $data = [
            'title' => __('Update Coin Network Setting'),
            'button_title' => __('Update'),
            'coin_network' => $coinNetwork,
            'network' => $network,
            'item' => $coin
        ];
        if ($data['item'])
            $data['coin_setting'] = CoinSetting::where([
                'coin_id' => $coinNetwork->currency_id,
                'network' => $coinNetwork->network_id
            ])->first(); // Only for bitgo & bitcoin api

        if (NetworkBase::isCoinPayment($network?->base_type)) {
            return ResponseFacade::success(__('Please wait while we redirect you to Coin Payment'))
                ->redirect_next('adminCoinApiSettings')->query(['tab' => 'payment'])->send();
        } else {
            return ResponseFacade::success($data)
                ->next_view('admin.coin_management.coin_network.edit_coin_network_settings')->send();
        }
    }

    public function coinNetworkDelete(string $id)
    {
        $response = $this->service->coinNetworkDelete($id);
        return ResponseFacade::result($response)->send();
    }
}
