<?php

namespace Modules\DemoTrade\Database\Seeders;

use App\Model\Coin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Modules\DemoTrade\Entities\DemoCoin;

class SeedDemoCoinsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $coins = Coin::get();
        foreach ($coins as $coin) {
            if(! DB::connection("DemoTradeMysql")->table("demo_coins")->where('coin_type', $coin->coin_type)->first())
            {
                DB::connection("DemoTradeMysql")->table("demo_coins")->insert([
                    'coin_id' => $coin->id,
                    'coin_type' => $coin->coin_type,
                    'trade_status' => $coin->is_demo_trade ?? 0,
                    'faucet_amount' => 1000,
                ]);
            }
        }
    }
}
