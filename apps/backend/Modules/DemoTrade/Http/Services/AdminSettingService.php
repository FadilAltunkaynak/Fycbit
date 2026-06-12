<?php

namespace Modules\DemoTrade\Http\Services;


use App\Model\Coin;
use App\Model\UserNavbar;
use App\Model\AdminSetting;
use App\Model\Announcement;
use App\Model\LandingBanner;
use App\Http\Services\Logger;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Services\CommonService;
use Modules\DemoTrade\Entities\CoinPair;
use App\Http\Repositories\SettingRepository;
use App\Http\Services\ChartThirdPartyApiService;
use App\Http\Repositories\AdminSettingRepository;


class AdminSettingService extends CommonService
{
    public $model = AdminSetting::class;
    public $repository = AdminSettingRepository::class;
    public $logger;
    public function __construct()
    {
        parent::__construct($this->model, $this->repository);
        $this->logger = new Logger();
    }



    public function savePairSetting($request)
    {
        try {
            if ($request->parent_coin_id == $request->child_coin_id) {
                return ['success' => false, 'message' => __('Same coin pair is not possible')];
            }
            $request->merge(['is_token' => checkNetworkCoinPrice($request->parent_coin_id, $request->child_coin_id)]);
            $coinPair = CoinPair::where(['parent_coin_id' => $request->parent_coin_id, 'child_coin_id' => $request->child_coin_id])->first();

            if (isset($request->edit_id)) {
                if (isset($coinPair) && ($coinPair->id != decrypt($request->edit_id))) {
                    return ['success' => false, 'message' => __('This coin pair already exist')];
                }

                $this->updateOrCreateCoinPair($request, $request->edit_id);
                $message = __('Updated Successfully.');
            } else {
                if (isset($coinPair)) {
                    return ['success' => false, 'message' => __('This coin pair already exist')];
                }

                $a = $this->updateOrCreateCoinPair($request);
                if ($a) {
                    $message = __('Added Successfully.');
                } else {
                    $message = __('Get price failed, please add the price manually');
                }
            }

            return ['success' => true, 'message' => $message];
        } catch (\Exception $e) {
            storeException('savePairSetting', $e->getMessage());
            return ['success' => false, 'data' => [], 'message' => __('Something went wrong')];
        }
    }

    public function changeCoinPairStatus($request)
    {
        try {
            $pair = CoinPair::find(decrypt($request->active_id));
            $success = false;
            $message = __('Pair not found');
            if (isset($pair)) {
                if ($pair->status == STATUS_ACTIVE) {
                    $pair->update(['status' => STATUS_INACTIVE]);
                } else {
                    $pair->update(['status' => STATUS_ACTIVE]);
                }
                $success = true;
                $message = __('Status updated successfully');
            }

            return ['success' => $success, 'message' => $message];
        } catch (\Exception $e) {
            storeException('changeCoinPairStatus', $e->getMessage());
            return ['success' => false, 'message' => __('Something went wrong')];
        }
    }

    public function changeCoinPairBotStatus($request)
    {
        try {
            $pair = CoinPair::find(decrypt($request->active_id));
            $success = false;
            $message = __('Pair not found');
            if (isset($pair)) {
                if ($pair->bot_trading == STATUS_ACTIVE) {
                    $pair->update(['bot_trading' => STATUS_INACTIVE]);
                } else {
                    $pair->update(['bot_trading' => STATUS_ACTIVE]);
                }
                $success = true;
                $message = __('Status updated successfully');
            }

            return ['success' => $success, 'message' => $message];
        } catch (\Exception $e) {
            storeException('changeCoinPairBotStatus', $e->getMessage());
            return ['success' => false, 'message' => __('Something went wrong')];
        }
    }

    // delete coin pair
    public function coinPairsDeleteProcess($id)
    {
        try {
            $coinPair = CoinPair::find($id);
            if ($coinPair) {
                $check = checkDemoCoinPairDeleteCondition($coinPair);
                if ($check['success'] == false) {
                    return ['success' => false, 'message' => $check['message']];
                }
                DB::connection('DemoTradeMysql')->table('coin_pairs')->where(['id' => $id])->delete();
                $response = ['success' => true, 'message' => __('Pair deleted successfully')];
            } else {
                $response = ['success' => false, 'message' => __('Pair not found')];
            }
        } catch (\Exception $e) {
            storeException('coinPairsDeleteProcess', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong')];
        }
        return $response;
    }

    public function updateOrCreateCoinPair($request, $edit_id = null)
    {
        $data = [
            'parent_coin_id' => $request->parent_coin_id,
            'child_coin_id' => $request->child_coin_id,
            'is_token' => $request->is_token
        ];
        if ($request->is_token == STATUS_ACTIVE) {
            $data['bot_trading'] = STATUS_PENDING;
        }
        if (isset($edit_id)) {
            $coinPair = CoinPair::where('id', decrypt($edit_id))->first();
            $data['is_token'] = STATUS_ACTIVE;
            $data['bot_possible'] = STATUS_INACTIVE;
            $data['bot_trading'] = STATUS_INACTIVE;
            if (isset($request->pair_listed_api) && $request->pair_listed_api == STATUS_ACTIVE) {
                $data['is_token'] = STATUS_INACTIVE;
                $data['bot_possible'] = STATUS_ACTIVE;
                $data['bot_trading'] = STATUS_ACTIVE;
            }
            if (isset($coinPair)) {
                if ($request->price) {
                    $data['price'] = $request->price;
                }
                return $coinPair->update($data);
            }
            return false;
        } else {
            $pair = get_coin_type($request->child_coin_id) . '_' . get_coin_type($request->parent_coin_id);
            $callApi = getPriceFromApi($pair);
            if ($callApi['success'] == true) {
                $data['bot_possible'] = STATUS_ACTIVE;
                $data['price'] = $callApi['data']['price'];
                $data['initial_price'] = $callApi['data']['price'];
                $create = CoinPair::create($data);
                return $create;
            } else {
                $data['bot_possible'] = STATUS_PENDING;
                if (isset($request->price) && $request->price > 0) {
                    $data['price'] = $request->price;
                    $data['initial_price'] = $request->price;
                    $create = CoinPair::create($data);
                    return $create;
                } else {
                    return false;
                }
            }

        }
    }

    public function coinPairsChartUpdate($id)
    {
        try {
            $coinPair = CoinPair::where(['id' => decryptId($id), 'is_chart_updated' => STATUS_PENDING])->first();
            if ($coinPair) {
                $apiData = $this->updateDataFromCryptoCompare($coinPair->parent_coin_id, $coinPair->child_coin_id);
                if ($apiData == TRUE) {
                    $coinPair->update(['is_chart_updated' => STATUS_SUCCESS]);
                    $response = responseData(true, __('Coin pair data added successfully'));
                } else {
                    $response = responseData(false, __('Data added failed'));
                }
            } else {
                $response = responseData(false, __('Coin pair not found'));
            }
        } catch (\Exception $e) {
            storeException('coinPairsChartUpdate', $e->getMessage());
            $response = responseData(false);
        }
        return $response;
    }

    public function updateDataFromCryptoCompare($baseCoinId, $tradeCoinId)
    {
        try {
            $baseCoin = get_coin_type($baseCoinId);
            $tradeCoin = get_coin_type($tradeCoinId);
            $apiKey = settings('CRYPTOCOMPARE_API_KEY') ?? '';
            $url = file_get_contents("https://min-api.cryptocompare.com/data/histominute?&fsym=$tradeCoin&tsym=$baseCoin&toTs=1665120104000&limit=2000&api_key=$apiKey");
            $data = json_decode($url, true);
            $input = [];
            if ($data['Response'] == "Success") {
                if (isset($data['Data'][0])) {
                    foreach ($data['Data'] as $item) {
                        $input[] = [
                            'base_coin_id' => $baseCoinId,
                            'trade_coin_id' => $tradeCoinId,
                            'interval' => $item['time'],
                            'open' => $item['open'],
                            'close' => $item['close'],
                            'high' => $item['high'],
                            'low' => $item['low'],
                            'volume' => $item['volumefrom'],
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                    }
                    DB::connection('DemoTradeMysql')->table('tv_chart_5mins')->insert($input);
                    DB::connection('DemoTradeMysql')->table('tv_chart_15mins')->insert($input);
                    DB::connection('DemoTradeMysql')->table('tv_chart_30mins')->insert($input);
                    DB::connection('DemoTradeMysql')->table('tv_chart_2hours')->insert($input);
                    DB::connection('DemoTradeMysql')->table('tv_chart_4hours')->insert($input);
                    DB::connection('DemoTradeMysql')->table('tv_chart_1days')->insert($input);
                    return true;
                } else {
                    storeException('updateDataFromCryptoCompare', 'no data found');
                    return false;
                }
            } else {
                storeException('updateDataFromCryptoCompare', $data['Message']);
                return false;
            }
        } catch (\Exception $e) {
            storeException('updateDataFromCryptoCompare', $e->getMessage());
            return false;
        }
    }
}
