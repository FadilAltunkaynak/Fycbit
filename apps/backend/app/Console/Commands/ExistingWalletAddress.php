<?php

namespace App\Console\Commands;

use App\Model\Coin;
use App\Model\Wallet;
use App\Model\WalletAddressHistory;
use Illuminate\Console\Command;

class ExistingWalletAddress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:existingwalletaddress';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Up date existing wallet key';

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
            $existingKeyWalletList = WalletAddressHistory::where('wallet_key', '!=', '')
                ->where('is_encrypted', 0)
                ->get();

            foreach ($existingKeyWalletList as $existingKeyWallet) {
                $decryptExistingWalletKey = get_wallet_personal_add($existingKeyWallet->address, $existingKeyWallet->wallet_key);

                if (!$decryptExistingWalletKey)
                    continue;

                $customEncrypt = custom_encrypt($decryptExistingWalletKey);

                $updateWalletHistory = WalletAddressHistory::where('id', $existingKeyWallet->id)
                    ->update([
                        'wallet_key' => $customEncrypt,
                        'is_encrypted' => 1
                    ]);
            }

            $walletAddressHistoryList = WalletAddressHistory::whereNull('network_id')
                ->whereNull('coin_id')->get();


            foreach ($walletAddressHistoryList as $walletAddressHistory) {
                $coinDetails = Coin::join('coin_networks', 'coins.id', '=', 'coin_networks.currency_id')
                    ->where('coins.coin_type', $walletAddressHistory->coin_type)
                    ->select('coin_networks.network_id', 'coin_networks.currency_id as coin_id')
                    ->first();

                if (!empty($coinDetails)) {
                    $wallet = Wallet::where(['id' => $walletAddressHistory->wallet_id])->first();

                    WalletAddressHistory::where('id', $walletAddressHistory->id)->update([
                        'coin_id' => $coinDetails->coin_id,
                        'network_id' => $coinDetails->network_id,
                        'user_id' => $wallet->user_id
                    ]);
                }
            }

        } catch (\Exception $e) {
            storeLog(processExceptionMsg($e), 'error');
        }
    }
}
