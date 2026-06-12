<?php

namespace App\Http\Controllers\admin;

use App\Model\Network;
use Illuminate\Http\Request;
use App\Model\AdminWalletKey;
use App\Http\Controllers\Controller;
use App\Http\Services\SystemWalletService;
use App\Http\Requests\Admin\CreateSystemWalletRequest;

class SystemWalletController extends Controller
{
    private $service;
    public function __construct()
    {
        $this->service = new SystemWalletService; 
    }

    public function getSystemWalletList(Request $request)
    {
        if($request->ajax()){
            $networks = AdminWalletKey::with("network");
            return datatables()->of($networks)
                ->addColumn('logo', function ($item) {
                    $logo = !empty($item?->network?->logo) ? asset(IMG_NETWORK_LOGO_PATH.$item->network->logo) : asset("assets/img/dlr.png");
                    return '<img src="'.$logo.'" height="40px">';
                })
                // ->addColumn('network', function ($item) {
                //     return $item?->network?->name;
                // })
                ->editColumn('status', function ($item) {
                    return '<label class="switch">
                                <input onchange="changeNetworkStatus('.$item->id.')" type="checkbox" name="status" '.(($item->status== STATUS_ACTIVE) ? "checked" : "").' /> 
                                <span class="slider"></span>
                            </label>';
                })
                ->editColumn('created_at', function ($item) {
                    return $item->created_at;
                })
                ->addColumn('actions', function ($item) {
                    return '<ul>'.edit_html("createSystemWallet" ,$item->id).'</ul>';
                })
                ->rawColumns(['logo','status','actions'])
                ->make(true);
        }
        $data['title'] = __("Wallet List");
        return view("admin.admin_wallet.admin_wallet", $data);
    }
    
    public function createSystemWallet($id = 0)
    {
        $data['title'] = __("Create System Wallet");
        if($id == 0)
            $data['networks'] = Network::whereDoesntHave('admin_wallet')
                                ->where("status", STATUS_ACTIVE)
                                ->whereNotIn("base_type",[COIN_PAYMENT, BITCOIN_API, BITGO_API])->get();
        else
            $data['networks'] = Network::where("status", STATUS_ACTIVE)->get();
        if($id != 0){
            $id = decryptId($id);
            if(!isset($id['success'])){
                if($network = AdminWalletKey::find($id)){
                    $data["item"] = $network;
                }
            }
        }
        return view("admin.admin_wallet.create_admin_wallet", $data);
    }

    public function createSystemWalletProccess(CreateSystemWalletRequest $request)
    {
        $response = $this->service->createSystemWalletProccess($request);
        if(isset($response['success']) && $response['success'])
            return redirect()->route("getSystemWalletList")->with("success", $response['message']);
        return redirect()->back()->with("dismiss", $response['message']);
    }

    public function changeSystemWalletStatus(Request $request){
        if(isset($request->id)){
            if($network = AdminWalletKey::find($request->id)){
                $network->status = !$network->status;
                if($network->save())
                return responseData(true, __("System wallet status changed successfully"));
                return responseData(false, __("System wallet failed to changed status"));
            }
            return responseData(false, __("System wallet not found"));
        }
        return responseData(false, __("System wallet id is missing"));
    }

    public function viewSystemWalletey(Request $request){
        return response()->json(
            $this->service->viewSystemWalletey($request)
        );
    }
    
    public function updateSystemWalletey(Request $request){
        return response()->json(
            $this->service->updateSystemWalletey($request)
        );
    }

    public function systemWalletCheckAddress(Request $request){
        return response()->json(
            $this->service->systemWalletCheckAddress($request)
        );
    }
}
