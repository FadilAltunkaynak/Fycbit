<?php

namespace App\Http\Controllers\admin;

use App\Facades\ResponseFacade;
use App\Model\Network;
use App\Model\SupportedNetwork;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Services\NetworkService;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Admin\CreateNetworkRequest;
use App\Http\Services\DataTable\NetworkDataTableService;
use App\Model\NotifiedBlock;

class NetworkController extends Controller
{
    private NetworkService $service;

    public function __construct()
    {
        $this->service = new NetworkService;
    }

    public function getNetworkList(Request $request)
    {
        if ($request->ajax())
            return (new NetworkDataTableService)->getData();

        $data['title'] = __("Network List");
        return view("admin.coin_management.network.network", $data);
    }

    public function createNetwork($id = 0)
    {
        $data['title'] = __("Create Network");
        if ($id != 0) {
            $data['title'] = __("Update Network");
            $id = decryptId($id);
            if (!isset($id['success'])) {
                if ($network = Network::with('block')->find($id)) {
                    $data["item"] = $network;
                    $data['network'] = SupportedNetwork::whereSlug($network->slug)->get(['name', 'slug']);
                    $data['block_error'] = null;
                    $NotifiedBlockError = NotifiedBlock::select('error')->where('network_id', $network->id)->first();
                    if ($NotifiedBlockError) {
                        $data['block_error'] = $NotifiedBlockError->error;
                    }
                }
            }
        } else {
            // $slugs = [];
            // Network::get(['slug'])->map(function($network) use(&$slugs){$slugs[] = $network->slug;});
            $data['network'] = SupportedNetwork::query()
                // ->whereNotIn('slug', $slugs)
                ->whereNotIn('type', [COIN_PAYMENT, BITCOIN_API, BITGO_API])
                ->get(['name', 'slug', 'base_url']);
        }
        return view("admin.coin_management.network.network_create", $data);
    }

    public function createNetworkProcess(CreateNetworkRequest $request)
    {
        $response = $this->service->createNetworkProcess($request);
        if (isset($response['success']) && $response['success'])
            return redirect()->route("getNetworkList")->with("success", $response['message']);
        return redirect()->back()->with("dismiss", $response['message']);
    }

    public function changeNetworkStatus(Request $request)
    {
        if (isset($request->id)) {
            if ($network = Network::find($request->id)) {
                $network->status = !$network->status;
                if ($network->save())
                    return responseData(true, __("Network status changed successfully"));
                return responseData(false, __("Network failed to changed status"));
            }
            return responseData(false, __("Network not found"));
        }
        return responseData(false, __("Network id is missing"));
    }

    // public function deleteNetwork(string $id): RedirectResponse
    // {
    //     $response = $this->service->deleteNetwork($id);
    //     if(isset($response['success']) && $response['success'])
    //         return redirect()->route("getNetworkList")->with("success", $response['message']);
    //     return redirect()->back()->with("dismiss", $response['message']);
    // }

    public function checkCurrentBlock(Request $request)
    {
        return $this->service->checkLatestBlock($request);
    }

    public function networksByCoinProvider(Request $request, $coin_id)
    {
        return $this->service->networksByCoinProvider($coin_id);
    }
}
