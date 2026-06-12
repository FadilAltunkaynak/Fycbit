<?php

namespace App\Console\Commands;

use App\Model\AdminWalletKey;
use App\Model\CoinSetting;
use App\Model\Network;
use Illuminate\Console\Command;

class UpdateSystemWallet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:updatesystemwallet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $coinSettingList = CoinSetting::whereNotNull('coin_settings.wallet_address')->get();

            foreach ($coinSettingList as $coin) {
                $network = Network::where([
                    'base_type' => getNetBaseType($coin->network),
                    'chain_id' => $coin->chain_id
                ])->first();

                if (!empty($network)) {
                    $key = decryptId($coin->wallet_key);
                    $key = (gettype($key) == 'array') ? false : $key;

                    if (!$key)
                        continue;

                    AdminWalletKey::firstOrCreate(['network_id' => $network->id], [
                        'uid' => generateUID(),
                        'address' => $coin->wallet_address,
                        'pv' => custom_encrypt($key),
                        'creation_type' => 0,
                        'status' => STATUS_ACTIVE
                    ]);
                }
            }

        } catch (\Exception $e) {
            storeException('UpdateSystemWallet', $e->getMessage());
        }
    }
}
