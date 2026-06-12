<?php

namespace Modules\P2P\Database\Seeders;

use App\Model\CurrencyList;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\P2P\Http\Repository\CurrencyRepository;

class CurrencySeederTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $repo = new CurrencyRepository;
        $currencys = CurrencyList::where("status", STATUS_ACTIVE)->get();
        if(isset($currencys[0])){
            foreach($currencys as $currency)
            {
                $data = [
                    "code" => $currency->code,
                    "minimum_price" => 1,
                    "maximum_price" => 100,
                    "trade_status" => STATUS_ACTIVE
                ];
                $repo->saveCurrencySetting((Object)$data);
            }
        }
    }
}
