<?php
namespace App\Http\Services;

use Aloha\Twilio\Twilio;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChartThirdPartyApiService
{

    public function __construct()
    {
        $sid = allsetting('twillo_secret_key');
        $token = allsetting('twillo_auth_token');
        $from = allsetting('twillo_number');
    }

    public function updateDataFromCryptoCompare($baseCoinId,$tradeCoinId)
    {
        try {
            $baseCoin = get_coin_type($baseCoinId);
            $tradeCoin = get_coin_type($tradeCoinId);
            $apiKey = settings('CRYPTOCOMPARE_API_KEY') ?? '';
            $url = file_get_contents("https://min-api.cryptocompare.com/data/histominute?&fsym=$tradeCoin&tsym=$baseCoin&toTs=1665120104000&limit=2000&api_key=$apiKey");
            $data = json_decode($url,true);
            $input = [];
            if ($data['Response'] == "Success") {
                if (isset($data['Data'][0])) {
                    foreach ($data['Data'] as $item) {
                        $input[] = [
                            'base_coin_id' => $baseCoinId,
                            'trade_coin_id' => $tradeCoinId,
                            'interval'=> $item['time'],
                            'open'=> $item['open'],
                            'close'=> $item['close'],
                            'high'=> $item['high'],
                            'low'=> $item['low'],
                            'volume'=> $item['volumefrom'],
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                    }
                    DB::table('tv_chart_5mins')->insert($input);
                    DB::table('tv_chart_15mins')->insert($input);
                    DB::table('tv_chart_30mins')->insert($input);
                    DB::table('tv_chart_2hours')->insert($input);
                    DB::table('tv_chart_4hours')->insert($input);
                    DB::table('tv_chart_1days')->insert($input);
                    return true;
                } else {
                    storeException('updateDataFromCryptoCompare', 'no data found');
                    return false;
                }
            } else {
                storeException('updateDataFromCryptoCompare',$data['Message']);
                return false;
            }
        } catch (\Exception $e) {
            storeException('updateDataFromCryptoCompare',$e->getMessage());
            return false;
        }
    }

    public function updateFutureDataFromCryptoCompare($baseCoinId, $tradeCoinId)
    {
        try {
            $baseCoin = get_coin_type($baseCoinId);
            $tradeCoin = get_coin_type($tradeCoinId);
            $apiKey = settings('CRYPTOCOMPARE_API_KEY') ?? '';
            $url = file_get_contents("https://min-api.cryptocompare.com/data/histominute?&fsym=$baseCoin&tsym=$tradeCoin&toTs=1665120104000&limit=2000&api_key=$apiKey");
            $data = json_decode($url, true);
            $input = [];
            if ($data['Response'] == "Success") {
                if (isset($data['Data'][0])) {
                    foreach ($data['Data'] as $item) {
                        $input[] = [
                            'base_coin_id' => $baseCoinId,
                            'trade_coin_id' => $tradeCoinId,
                            'interval'=> $item['time'],
                            'open'=> $item['open'],
                            'close'=> $item['close'],
                            'high'=> $item['high'],
                            'low'=> $item['low'],
                            'volume'=> $item['volumefrom'],
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                    }

                    $uniqueBy = ['base_coin_id', 'trade_coin_id', 'interval'];
                    $update = ['open', 'close', 'high', 'low', 'volume', 'updated_at'];

                    DB::table('future_five_minute_charts')->upsert($input, $uniqueBy, $update);
                    DB::table('future_fifteen_minute_charts')->upsert($input, $uniqueBy, $update);
                    DB::table('future_thirty_minute_charts')->upsert($input, $uniqueBy, $update);
                    DB::table('future_two_hour_charts')->upsert($input, $uniqueBy, $update);
                    DB::table('future_four_hour_charts')->upsert($input, $uniqueBy, $update);
                    DB::table('future_one_day_charts')->upsert($input, $uniqueBy, $update);
                    return true;
                } else {
                    storeException('updateFutureDataFromCryptoCompare', 'no data found');
                    return false;
                }
            } else {
                storeException('updateFutureDataFromCryptoCompare', $data['Message']);
                return false;
            }
        } catch (\Exception $e) {
            storeException('updateFutureDataFromCryptoCompare', $e->getMessage());
            return false;
        }
    }
}
