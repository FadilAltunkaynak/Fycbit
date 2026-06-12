<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Model\SupportedNetwork;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateNewSupportedNetworkRequest;
use App\Http\Services\DataTable\SupportedNetworkDataTableService;

class supportedNetworkController extends Controller
{
    public function __construct() {}

    public function supportedNetwork(Request $request)
    {
        if ($request->ajax())
            return (new SupportedNetworkDataTableService)->getData();

        $data['title'] = __("Supported Network");
        return view('admin.coin_management.supported_network.list', $data);
    }

    public function supportedNetworkStatus(Request $request)
    {
        if (isset($request->id)) {
            if ($network = SupportedNetwork::find($request->id)) {
                $network->status = !$network->status;
                if ($network->save())
                    return responseData(true, __("Network status changed successfully"));
                return responseData(false, __("Network failed to changed status"));
            }
            return responseData(false, __("Network not found"));
        }
        return responseData(false, __("Network id is missing"));
    }

    public function supportedNetworkEdit($id)
    {
        $id = decryptId($id);
        $id = gettype($id) == 'array' ? 0 : $id;
        if ($data['item'] = SupportedNetwork::find($id)) {
            $data['title'] = __("Network Edit");
            return view('admin.coin_management.supported_network.edit', $data);
        }
        return redirect()->back()->with("dismiss", __("Network not found"));
    }

    public function supportedNetworkEditProccess(Request $request)
    {
        if ($network = SupportedNetwork::find($request->id ?? 0)) {
            $status = isset($request->status) ? 1 : 0;
            $network->status = $status;

            if (isset($request->gas_limit))
                $network->gas_limit = $request->gas_limit;

            if (isset($request->tx_endpoint))
                $network->tx_endpoint = $request->tx_endpoint;

            if (isset($request->token_endpoint))
                $network->token_endpoint = $request->token_endpoint;

            if (isset($request->address_endpoint))
                $network->address_endpoint = $request->address_endpoint;

            if (isset($request->base_url))
                $network->base_url = $request->base_url;

            if ($network->save())
                redirect()->route("supportedNetworkList")->with("success", __("Supported Network updated successfully"));
            redirect()->route("supportedNetworkList")->with("dismiss", __("Supported Network failed to update"));
        }
        return redirect()->back()->with("dismiss", __("Network not found"));
    }

    public function creteSupportedNetworkPage(Request $request)
    {
        if ($request->slug) {
            if (!$supportNetwork = SupportedNetwork::where('slug', $request->slug)->first())
                redirect()->route("supportedNetworkList")->with("dismiss", __("Supported network not found"));

            $data['item'] = $supportNetwork;
        }
        $data['title'] = __("Add New Supported Network");
        return view('admin.coin_management.supported_network.create', $data);
    }

    public function creteSupportedNetwork(CreateNewSupportedNetworkRequest $request)
    {

        $supportNetworkData = [
            'type' => $request->type,
            'slug' => make_unique_slug($request->name),
            'name' => $request->name,
            'network_type' => $request->environment,
            'chain_id' => $request->chain_id,
            'native_currency' => $request->native_currency,
            'base_url' => $request->base_url,
            'token_endpoint' => $request->token_endpoint,
            'address_endpoint' => $request->address_endpoint,
            'tx_endpoint' => $request->tx_endpoint,
            'gas_limit' => $request->gas_limit,
            'gas_price' => $request->gas_price,
            'status' => isset($request->status),
            "is_manually" => true,
        ];

        try {
            $supportedNetwork =  SupportedNetwork::create($supportNetworkData);
            if ($supportedNetwork)
                return redirect()->route("supportedNetworkList")->with("success", __("Supported network added successfully"));
            return redirect()->route("creteSupportedNetworkPage")->with("dismiss", __("Supported network failed to add"));
        } catch (\Exception $e) {
            storeException("creteSupportedNetwork", $e->getMessage());
            return redirect()->route("creteSupportedNetworkPage")->with("dismiss", $e->getMessage());
        }
    }
    public function editSupportedNetwork(Request $request)
    {
        if (!$supportedNetwork = SupportedNetwork::where("slug", $request->slug)->first())
            return redirect()->route("creteSupportedNetworkPage")->with("dismiss", __("Supported network not found"));

        $supportNetworkData = [
            'type' => $request->type,
            'name' => $request->name,
            'network_type' => $request->environment,
            'chain_id' => $request->chain_id,
            'native_currency' => $request->native_currency,
            'base_url' => $request->base_url,
            'token_endpoint' => $request->token_endpoint,
            'address_endpoint' => $request->address_endpoint,
            'tx_endpoint' => $request->tx_endpoint,
            'gas_limit' => $request->gas_limit,
            'gas_price' => $request->gas_price,
            'status' => isset($request->status),
        ];

        try {
            if ($supportedNetwork->update($supportNetworkData))
                return redirect()->route("supportedNetworkList")->with("success", __("Supported network updated successfully"));
            return redirect()->route("creteSupportedNetworkPage")->with("dismiss", __("Supported network failed to update"));
        } catch (\Exception $e) {
            storeException("creteSupportedNetwork", $e->getMessage());
            return redirect()->route("creteSupportedNetworkPage")->with("dismiss", $e->getMessage());
        }
    }
}
