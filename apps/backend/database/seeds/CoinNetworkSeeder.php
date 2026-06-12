<?php

namespace Database\Seeders;

use App\Model\Network;
use App\Model\CoinNetwork;
use App\Model\CoinSetting;
use Illuminate\Database\Seeder;

class CoinNetworkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $coins = CoinSetting::all();

        foreach ($coins as $coin) {
            if (empty($coin->network))
                continue;

            $networkGetConditions = [];
            $coinType = TOKEN_COIN;
            $contractAddress = "";

            switch ($coin->network) {
                case COIN_PAYMENT:
                    $networkGetConditions = ['slug' => 'coin_payment'];
                    $coinType = NATIVE_COIN;
                    break;
                case BITCOIN_API:
                    $networkGetConditions = ['slug' => 'bitcoin_api'];
                    $coinType = NATIVE_COIN;
                    break;
                case BITGO_API:
                    $networkGetConditions = ['slug' => 'bitgo_api'];
                    $coinType = NATIVE_COIN;
                    break;

                default:
                    $networkGetConditions = [
                        'base_type' => getNetBaseType($coin->network),
                        'chain_id' => $coin->chain_id
                    ];
                    $coinType = TOKEN_COIN;
                    $contractAddress = $coin->contract_address ?: "";
                    break;
            }

            $network = Network::where($networkGetConditions)->first();

            if ($network) {
                CoinNetwork::firstOrCreate(["network_id" => $network->id, "currency_id" => $coin->coin_id], [
                    "uid" => uniqid() . time(),
                    "type" => $coinType,
                    "contract_address" => $contractAddress,
                    "status" => STATUS_ACTIVE
                ]);
            }
        }
    }
}
