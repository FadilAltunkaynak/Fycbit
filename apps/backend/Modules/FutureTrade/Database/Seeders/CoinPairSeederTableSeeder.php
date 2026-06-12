<?php

namespace Modules\FutureTrade\Database\Seeders;

use App\Model\AdminSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\FutureTrade\Entities\FutureCoinPair;
use Modules\FutureTrade\Entities\FutureLeverageSetting;

class CoinPairSeederTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function(){

            if(! FutureCoinPair::exists()){
                $coinPair = FutureCoinPair::create([
                    'uid' => Str::uuid()->getHex(),
                    'base_coin_id' => 1, // BTC
                    'trade_coin_id' => 2, // USDT
                    'code' => 'BTCUSDT',
                    'base_coin_code' => 'BTC',
                    'trade_coin_code' => 'USDT',
                    'base_decimal' => 6,
                    'trade_decimal' => 6,
                    'min_amount' => 0.0000000001,
                    'max_amount' => 1000,
                    'maker_fees_percent' => 0.2,
                    'taker_fees_percent' => 0.5,
                    'status' => 1,
                    'min_stop_limit_percent' => 15,
                    'max_stop_limit_percent' => 20,
                    'cap_ratio' => 5,
                    'floor_ratio' => 5,
                    'leverage' => 10,
                    'max_leverage' => 120,
                    'max_open_orders' => 10,
                    'funding_rate' => 0.01,
                    'funding_next_time' => now()->addHours(8),
                    'slippage_percent' => 0.1,
                ]);
    
                AdminSetting::updateOrCreate(
                    ['slug' => 'future_trade_default_coin_pair'],
                    ['value' => $coinPair->id]
                );
    
                $data = [
                    [0, 50000, 125, 0.4, 0],
                    [50001, 500000, 100, 0.5, 50],
                    [500001, 10000000, 50, 0.01, 2550],
                    [10000001, 80000000, 20, 0.025, 152550],
                    [80000001, 150000000, 10, 0.05, 2152550],
                    [150000001, 300000000, 5, 0.10, 9652550],
                    [300000001, 450000000, 4, 0.125, 17152550],
                    [450000001, 600000000, 3, 0.15, 28402550],
                    [600000001, 800000000, 2, 0.25, 88402550],
                    [800000001, 1000000000, 1, 0.50, 288402550],
                ];

                foreach ($data as $row) {
                    FutureLeverageSetting::create([
                        'uid' => Str::uuid()->getHex(),
                        'coin_pair_uid' => $coinPair->uid,
                        'min_position_amount' => $row[0],
                        'max_position_amount' => $row[1],
                        'max_leverage' => $row[2],
                        'maintenance_margin_rate' => $row[3],
                        'maintenance_amount' => $row[4],
                    ]);
                }
            }
        });
    }
}
