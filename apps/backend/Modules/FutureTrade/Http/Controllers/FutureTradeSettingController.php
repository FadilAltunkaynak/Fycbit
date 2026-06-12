<?php

namespace Modules\FutureTrade\Http\Controllers;

use App\Model\AdminSetting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FutureTrade\Entities\FutureCoinPair;
use Modules\FutureTrade\Emum\FutureCoinPairStatusEnum;

class FutureTradeSettingController extends Controller
{
    public function index()
    {
        $data['title'] = __('Future Trade Settings');
        $data['coin_pairs'] = FutureCoinPair::where('status', FutureCoinPairStatusEnum::ACTIVE->value)->get();
        $data['settings'] = allsetting(['future_trade_default_coin_pair']);

        return view('futureTrade::settings.index', $data);
    }

    public function save(Request $request)
    {
        $rules = [
            'future_trade_default_coin_pair' => 'required|exists:future_coin_pairs,id'
        ];

        $request->validate($rules);

        try {
            AdminSetting::updateOrCreate(
                ['slug' => 'future_trade_default_coin_pair'],
                ['value' => $request->future_trade_default_coin_pair]
            );

            return redirect()->back()->with('success', __('Settings updated successfully'));
        } catch (\Exception $e) {
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }
}
