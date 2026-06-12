<?php

namespace Modules\DemoTrade\Http\Controllers;

use App\Model\Coin;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\DemoTrade\Entities\CoinPair;
use Modules\DemoTrade\Entities\DemoCoin;
use Modules\DemoTrade\Http\Requests\CoinPairRequest;
use Modules\DemoTrade\Http\Services\CoinPairService;
use Modules\DemoTrade\Http\Requests\CoinSettingRequest;
use Modules\DemoTrade\Http\Services\AdminSettingService;

class CoinController extends Controller
{
    private $coinPairService;

    public function __construct()
    {
        $this->coinPairService = new CoinPairService;
    }
    
    /*
   *
   * coin pair List
   * Show the list of specified resource.
   * @return \Illuminate\Http\Response
   *
   */
    public function coinPairs(Request $request)
    {
        $data['title'] = __('Coin Pair List');
        $data['coins'] = Coin::where(['is_base'=>STATUS_ACTIVE, 'is_demo_trade'=>STATUS_ACTIVE, 'status'=>STATUS_ACTIVE])->get();
        updateDemoCoins();
        $data['items'] = CoinPair::orderBy('id','desc')->get();

        return view('demotrade::coin_pairs.list', $data);
    }

    /**
     * saveCoinPairSettings
     *
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function saveCoinPairSettings(CoinPairRequest $request)
    {
        $adminSettingService = new AdminSettingService();
        $update = $adminSettingService->savePairSetting($request);

        if (isset($update) && $update['success'] == true) {
            return redirect()->back()->with(['success' => $update['message']]);
        }

        return redirect()->back()->with(['dismiss' => $update['message']]);
    }

    /**
     * changeCoinPairStatus
     *
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *
     */
    public function changeCoinPairStatus(Request $request)
    {
        $adminSettingService = new AdminSettingService();
        $update = $adminSettingService->changeCoinPairStatus($request);

        return response()->json(['success' => $update['success'], 'message' => $update['message']]);
    }

    public function changeCoinPairBotStatus(Request $request)
    {
        $adminSettingService = new AdminSettingService();
        $update = $adminSettingService->changeCoinPairBotStatus($request);

        return response()->json(['success' => $update['success'], 'message' => $update['message']]);
    }


    public function coinPairsDelete($id)
    {
        try {
            $coinId = decryptId($id);
            if(is_array($coinId)) {
                return redirect()->back()->with(['dismiss' => __('Coin pair not found')]);
            }
            $adminSettingService = new AdminSettingService();
            $update = $adminSettingService->coinPairsDeleteProcess($coinId);
            if ($update['success'] == true) {
                return redirect()->back()->with(['success' => $update['message']]);
            } else {
                return redirect()->back()->with(['dismiss' => $update['message']]);
            }
        } catch (\Exception $e) {
            storeException('coinPairsDelete', $e->getMessage());
            return redirect()->back()->with(['dismiss' => __('Something went wrong')]);
        }
    }

    public function coinPairsChartUpdate($id)
    {
        $adminSettingService = new AdminSettingService();
        $update = $adminSettingService->coinPairsChartUpdate($id);

        if (isset($update) && $update['success'] == true) {
            return redirect()->back()->with(['success' => $update['message']]);
        }

        return redirect()->back()->with(['dismiss' => $update['message']]);
    }


    public function coinList(Request $request)
    {
        try {
            updateDemoCoins();
            if($request->ajax())
            {
                $coins = DemoCoin::where('trade_status' , STATUS_ACTIVE)->get();
                return datatables()->of($coins)
                    ->addColumn('coin_type', function ($item) {
                        return $item->coin_type;
                    })
                    ->addColumn('faucet_amount', function ($query) {
                        return number_format($query->faucet_amount, 8);
                    })
                    ->addColumn('action', function ($query) {
                        return '<a href="'.route("demoCoinEdit",['coin_type' => $query->coin_type]).'" class="btn btn-primary">'.__("Edit").'</a>';
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }
        } catch (\Exception $e) {
            storeException('coinList p2p',$e->getMessage());
        }
        $data['title'] = __("Coin List");
        return view('demotrade::coins.list',$data);
    }

    public function coinEdit($coin_type)
    {
        $data = [];
        try {
            $data['title'] = __("Coin Setting");
            $data['coin_type'] = $coin_type;
            $response = $this->coinPairService->getCoinDetailsByType($coin_type);
            if (isset($response['success']) && $response['success']) {
                $data['coin'] = $response['data'];
            }
        } catch (\Exception $e) {
            storeException('coinEditProcess', $e->getMessage());
        }
        return view('demotrade::coins.edit', $data);
    }

    public function coinEditProcess(CoinSettingRequest $request)
    {
        try {
            $response = $this->coinPairService->saveCoinSetting($request);
            if (isset($response['success']) && $response['success']) {
                return redirect()->back()->with(['success' => $response['message']]);
            } return redirect()->back()->with(['dismiss' => $response['message']]);
        } catch (\Exception $e) {
            storeException('coinEditProcess', $e->getMessage());
            return redirect()->back()->with(['dismiss' => __("Something went wrong")]);
        }
    }

}
