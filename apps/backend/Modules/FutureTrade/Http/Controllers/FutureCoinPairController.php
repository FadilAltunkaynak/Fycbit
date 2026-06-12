<?php

namespace Modules\FutureTrade\Http\Controllers;

use App\Facades\ResponseFacade;
use App\Model\Coin;
use App\Http\Services\ChartThirdPartyApiService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\FutureTrade\Entities\FutureBotSetting;
use Modules\FutureTrade\Entities\FutureBuy;
use Modules\FutureTrade\Entities\FutureCoinPair;
use Modules\FutureTrade\Entities\FutureSell;
use Modules\FutureTrade\Entities\FutureTrade;
use Modules\FutureTrade\Http\Requests\FutureCoinPairBotSettingRequest;
use Modules\FutureTrade\Http\Requests\FutureCoinPairRequest;
use Modules\FutureTrade\Services\CoinPairService\CoinPairService;

class FutureCoinPairController extends Controller
{
    public function list()
    {
        if (IS_API_CALL) {
            $query = FutureCoinPair::with(['tradeCoin', 'baseCoin', 'botSetting']);
            return datatables($query)
                ->editColumn('code', 'futureTrade::coin-pairs.components.code-link')
                ->editColumn('trade_coin_code', 'futureTrade::coin-pairs.components.tradecoin-name-icon')
                ->editColumn('base_coin_code', 'futureTrade::coin-pairs.components.basecoin-name-icon')
                ->editColumn('created_at', '{{ date("d-m-Y H:i", strtotime($created_at)) }}')
                ->addColumn('bot_status', function ($item) {
                    $enabled = (int) ($item?->botSetting?->status ?? 0) === 1;
                    $class = $enabled ? 'badge badge-success' : 'badge badge-secondary';
                    $label = $enabled ? __('Enabled') : __('Disabled');

                    return '<span class="' . $class . '">' . $label . '</span>';
                })
                ->addColumn('status', function ($item) {
                    return view('admin.common.toggle', [
                        'status' => $item?->status?->value,
                        'attributes' => [
                            'onclick' => "changeCoinStatus(event,'{$item->uid}')"
                        ]
                    ])->render();
                })
                ->editColumn('action', 'futureTrade::coin-pairs.components.action')
                ->rawColumns(['code', 'base_coin_code', 'trade_coin_code', 'bot_status', 'status', 'action'])
                ->make(true);
        }
        $data['title'] = __('Future Coin Pairs');
        return view('futureTrade::coin-pairs.list', $data);
    }

    public function store(FutureCoinPairRequest $request, CoinPairService $service)
    {
        $response = $service->storeFutureCoinPair($request);

        if (!$request->filled('coin_pair_id')) {
            return ResponseFacade::result($response)->redirect_next('future.leverage.edit')
                ->query([
                    'coin_pair_uid' => FutureCoinPair::where([
                        'base_coin_code' => $request->base_coin_code,
                        'trade_coin_code' => $request->trade_coin_code,
                    ])->first()?->uid ?? "0"
                ])
                ->send();
        }

        return ResponseFacade::result($response)->send();
    }

    public function edit(string $uid = "")
    {
        $data['item'] = FutureCoinPair::where('uid', $uid)->first();
        $data['tradeCoins'] = Coin::where('coin_type', 'USDT')->select('id', 'coin_type')->get();
        $data['baseCoins'] = Coin::where([
            'currency_type' => CURRENCY_TYPE_CRYPTO,
            'status' => STATUS_ACTIVE,
        ])->where('coin_type', '!=', 'USDT')->select('id', 'coin_type')->get();

        return view('futureTrade::coin-pairs.edit', $data);
    }

    public function editBotSettings(string $uid)
    {
        $coinPair = FutureCoinPair::with('botSetting')->where('uid', $uid)->first();
        if (!$coinPair) {
            return redirect()->route('future.coin-pairs.list')->with('dismiss', __('Coin pair not found'));
        }

        return view('futureTrade::coin-pairs.bot-settings', [
            'item' => $coinPair,
            'botSetting' => $coinPair->botSetting,
        ]);
    }

    public function updateBotSettings(FutureCoinPairBotSettingRequest $request, string $uid)
    {
        $coinPair = FutureCoinPair::where('uid', $uid)->first();
        if (!$coinPair) {
            return redirect()->route('future.coin-pairs.list')->with('dismiss', __('Coin pair not found'));
        }

        FutureBotSetting::updateOrCreate([
            'future_coin_pair_id' => $coinPair->id,
        ], [
            'uid' => optional($coinPair->botSetting)->uid ?: Str::uuid()->toString(),
            'amount_low' => $request->bot_amount_low ?: 0,
            'amount_high' => $request->bot_amount_high ?: 0,
            'price_low' => $request->bot_price_low ?: 0,
            'price_high' => $request->bot_price_high ?: 0,
            'order_interval' => $request->bot_order_interval,
            'status' => $request->bot_status,
        ]);

        return redirect()
            ->route('future.coin-pairs.bot-settings.edit', ['uid' => $uid])
            ->with('success', __('Bot settings updated successfully'));
    }

    public function changeStatus(Request $request, CoinPairService $service)
    {
        $response = $service->updateCoinPairStatus($request->uid ?? "");
        return ResponseFacade::result($response)->send();
    }

    public function delete(string $uid, CoinPairService $service)
    {
        $response = $service->deleteCoinPair($uid);
        return ResponseFacade::result($response)->send();
    }

    public function chartUpdate(string $uid)
    {
        try {
            $coinPair = FutureCoinPair::where(['uid' => $uid, 'is_chart_updated' => STATUS_PENDING])->first();
            if (!$coinPair) {
                return redirect()->back()->with(['dismiss' => __('Coin pair not found')]);
            }

            $chartApi = new ChartThirdPartyApiService();
            $apiData = $chartApi->updateFutureDataFromCryptoCompare($coinPair->base_coin_id, $coinPair->trade_coin_id);

            if ($apiData === true) {
                $coinPair->update(['is_chart_updated' => STATUS_SUCCESS]);
                return redirect()->back()->with(['success' => __('Coin pair data added successfully')]);
            }

            return redirect()->back()->with(['dismiss' => __('Data added failed')]);
        } catch (\Exception $e) {
            storeException('futureCoinPairsChartUpdate', $e->getMessage());
            return redirect()->back()->with(['dismiss' => __('Something went wrong')]);
        }
    }

    public function cleanChartData(string $uid)
    {
        $coinPair = FutureCoinPair::where('uid', $uid)->first();
        if (!$coinPair) {
            return redirect()->back()->with(['dismiss' => __('Coin pair not found')]);
        }

        try {
            $where = [
                'base_coin_id' => $coinPair->base_coin_id,
                'trade_coin_id' => $coinPair->trade_coin_id,
            ];

            DB::table('future_five_minute_charts')->where($where)->delete();
            DB::table('future_fifteen_minute_charts')->where($where)->delete();
            DB::table('future_thirty_minute_charts')->where($where)->delete();
            DB::table('future_two_hour_charts')->where($where)->delete();
            DB::table('future_four_hour_charts')->where($where)->delete();
            DB::table('future_one_day_charts')->where($where)->delete();

            $coinPair->update(['is_chart_updated' => STATUS_PENDING]);

            return redirect()->back()->with(['success' => __('Chart data cleaned successfully')]);
        } catch (\Exception $e) {
            storeException('futureCoinPairsCleanChartData', $e->getMessage());
            return redirect()->back()->with(['dismiss' => __('Something went wrong')]);
        }
    }

    public function cleanBotData(string $uid)
    {
        $coinPair = FutureCoinPair::where('uid', $uid)->first();
        if (!$coinPair) {
            return redirect()->back()->with(['dismiss' => __('Coin pair not found')]);
        }

        try {
            FutureBuy::where('future_coin_pair_id', $coinPair->id)->where('is_bot', 1)->delete();
            FutureSell::where('future_coin_pair_id', $coinPair->id)->where('is_bot', 1)->delete();
            FutureTrade::where('future_coin_pair_id', $coinPair->id)->where('is_bot', 1)->delete();

            return redirect()->back()->with(['success' => __('Bot data cleaned successfully')]);
        } catch (\Exception $e) {
            storeException('futureCoinPairsCleanBotData', $e->getMessage());
            return redirect()->back()->with(['dismiss' => __('Something went wrong')]);
        }
    }
}
