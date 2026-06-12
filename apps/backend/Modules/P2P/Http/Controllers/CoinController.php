<?php

namespace Modules\P2P\Http\Controllers;

use App\Model\Coin;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\P2P\Entities\PCoinSetting;
use Modules\P2P\Http\Service\CoinService;
use Modules\P2P\Http\Repository\CoinRepository;
use Modules\P2P\Http\Requests\CoinSettingRequest;

class CoinController extends BaseController
{
    private $service; 

    public function __construct()
    {
        $this->service = new CoinService();
    }
    public function coinList(Request $request)
    {
        try {
            if($request->ajax())
            {
                $coins = $this->service->getAllActiveCoin();
                if (isset($coins['success']) && !$coins['success'])
                    return $this->sendBackResponse($coins, [], 1);
                return datatables()->of($coins['data'])
                    ->addColumn('name', function ($query) {
                        return $query->name;
                    })
                    ->addColumn('coin_type', function ($query) {
                        return find_coin_type($query->coin_type);
                    })
                    ->addColumn('network', function ($query) {
                        return api_settings($query->network);
                    })
                    ->addColumn('price', function ($query) {
                        return number_format($query->coin_price,2).' USD/ '.find_coin_type($query->coin_type);
                    })
                    ->editColumn('status', function ($query) {
                        $selected = ($query->p_status == STATUS_ACTIVE) ? "checked" : "";
                        return '
                            <div>
                                <label class="switch">
                                    <input type="checkbox" onclick="changeCoinStatus(\''.$query->coin_type.'\')"
                                        id="notification" name="security" '.$selected.' >
                                    <span class="slider" for="status"></span>
                                </label>
                            </div>
                        ';
                    })
                    ->addColumn('action', function ($query) {
                        return ActionButtonForList_p2p($query->id, 'p2pCoinEdit','',['id'=>['coin_type' => $query->coin_type],'delete'=>false]);
                    })
                    ->rawColumns(['status','action'])
                    ->make(true);
            }
        } catch (\Exception $e) {
            storeException('coinList p2p',$e->getMessage());
        }
        $data['title'] = __("Coin List");
        return view('p2p::coin.list',$data);
    }

    public function coinEdit($coin_type)
    {
        $data = [];
        try {
            $data['title'] = __("Coin Setting");
            $data['coin_type'] = $coin_type;
            $response = $this->service->getCoinDetailsByType($coin_type);
            if ($response['success']) $data['setting'] = $response['data'];
        } catch (\Exception $e) {
            storeException('coinEditProcess', $e->getMessage());
        }
        return view('p2p::coin.edit', $data);
    }

    public function coinEditProcess(CoinSettingRequest $request)
    {
        try {
            $response = $this->service->saveCoinSetting($request);
            return $this->sendBackResponse($response,['route' => 'p2pCoinList']);
        } catch (\Exception $e) {
            storeException('coinEditProcess', $e->getMessage());
            return responseData(false,__("Something went wrong"));
        }
    }

    public function coinStatusChange (Request $request)
    {
        // check coin in request params
        if(! isset($request->coin))
            return responseData(false,__("Coin is required"));
        
        // check p2p coin setting table
        if(! $coin = PCoinSetting::where('coin_type', $request->coin)->first())
            return responseData(false,__("Coin not found"));

        // set status
        $coin->trade_status = !$coin->trade_status;

        // status save and return success resposne
        if($coin->save()) return responseData(true,__("Status updated successfully"));

        // return failed resposne
        return responseData(false,__("Failed to update status"));
    }

    public function fetchNewCoins()
    {
        $repo = new CoinRepository();

        $coins = Coin::where("status", STATUS_ACTIVE)->get();
        foreach($coins as $coin)
        {
            $data = [
                "coin_type" => $coin->coin_type,
                "minimum_price" => 1,
                "maximum_price" => 100,
                "buy_fees" => 0,
                "sell_fees" => 0,
                "trade_status" => STATUS_ACTIVE
            ];
            $repo->saveCoinSetting((Object)$data);
        }

        return $this->sendBackResponse(['success' => true, "message" => __("Coin fetched successfully")]);
    }
}
