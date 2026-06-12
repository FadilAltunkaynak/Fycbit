<?php

namespace Modules\FutureTrade\Http\Controllers;

use App\Facades\ResponseFacade;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\FutureTrade\Entities\FutureLeverageSetting;
use Modules\FutureTrade\Http\Requests\FutureLeverageSettingsRequest;
use Modules\FutureTrade\Repositories\CoinPairRepository\CoinPairRepository;
use Modules\FutureTrade\Services\CoinPairService\CoinPairService;
use Modules\FutureTrade\Services\LeverageSettingService\LeverageSettingService;

class FutureLeverageSettingController extends Controller
{
    public function list(string $coin_pair_uid)
    {
        if(IS_API_CALL) {
            $query = FutureLeverageSetting::query()
                ->where('coin_pair_uid', $coin_pair_uid)
                ->orderBy('min_position_amount', 'asc');

            return datatables($query)
            ->addColumn('position_range', '{{ trim_num($min_position_amount) }} - {{ trim_num($max_position_amount, 1) }}')
            ->editColumn('max_leverage', '{{ $max_leverage }}x')
            ->editColumn('maintenance_margin_rate', '{{ trim_num($maintenance_margin_rate, 8) }}%')
            ->editColumn('maintenance_amount', '{{ trim_num($maintenance_amount, 8) }}')
            ->editColumn('action', 'futureTrade::leverage.components.action')
            ->rawColumns(['action'])
            ->make(true);
        }
        $coin_pair = CoinPairRepository::getCoinPairByUid($coin_pair_uid);
        if(!$coin_pair) return ResponseFacade::failed(__("Coin pair not found"))->send();

        $data['title'] = __('Future Coin Pairs');
        $data['coinPair'] = $coin_pair;
        return view('futureTrade::leverage.list', $data);
    }

    public function store(FutureLeverageSettingsRequest $request, LeverageSettingService $service) {
        $response = $service->leverageSettingSave($request);
        return ResponseFacade::result($response)
            ->redirect_next('future.leverage.list')
            ->query(['coin_pair_uid' => $request->coin_pair_uid])
            ->send();
    }

    public function edit(LeverageSettingService $service, string $coin_pair_uid, string $uid = "")
    {
        $coin_pair = CoinPairRepository::getCoinPairByUid($coin_pair_uid);
        if(!$coin_pair) return ResponseFacade::failed(__("Coin pair not found"))->send();
        $item = FutureLeverageSetting::where([
            'coin_pair_uid' => $coin_pair_uid,
            'uid' => $uid
        ])->first();

        return view('futureTrade::leverage.edit', compact('coin_pair', 'item'));
    }

    public function delete(string $coin_pair_uid, string $uid, LeverageSettingService $service)
    {
        $response = $service->leverageSettingDelete($coin_pair_uid, $uid);
        return ResponseFacade::result($response)
            ->redirect_next('future.leverage.list')
            ->query(['coin_pair_uid' => $coin_pair_uid])
            ->send();
    }
}
