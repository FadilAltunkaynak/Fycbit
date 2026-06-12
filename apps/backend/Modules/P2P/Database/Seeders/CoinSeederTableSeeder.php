<?php

namespace Modules\P2P\Database\Seeders;

use App\Model\Coin;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\P2P\Http\Repository\CoinRepository;

class CoinSeederTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $repo = new CoinRepository();

        $coins = Coin::where("status", STATUS_ACTIVE)->get();
        if(isset($coins[0])){
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
        }
    }
}
