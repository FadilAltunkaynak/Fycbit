<?php

namespace Modules\P2P\Jobs;

use App\Model\Wallet;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Modules\P2P\Entities\P2PWallet;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Database\Console\Migrations\RollbackCommand;
use Illuminate\Support\Facades\Log;
use Modules\P2P\Services\WalletFinderService;
use Throwable;

class WalletBalanceTransfer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

   

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private $wallet,
        private $amount,
        private $type,
        private $coin,
        private $user_id,
    ){}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(WalletFinderService $walletFinderService)
    {   
        if(!in_array(
            $this->type,
            [P2PWallet::WALLET_BALANCE_TRANSFER_RECEIVE, P2PWallet::WALLET_BALANCE_TRANSFER_SEND]
        )) {
            Log::error("Balance transfer error: Transfer type not found");
            return;
        }

        DB::beginTransaction();

        try{

            $receiverWallet = $walletFinderService->findReceiverWallet(
                $this->user_id,
                $this->coin,
                $this->type,
            );

            if(empty($receiverWallet)) 
            {
                Log::error("Balance transfer error: Could not find receiver wallet");
                throw new \Exception('Could not find receiver wallet');
            }

            $senderWallet = $walletFinderService->findAndLockSenderWalletById(
                $this->wallet->id,
                $this->type
            );

            if(empty($senderWallet)) {
                Log::error("Balance transfer error: Could not find sender wallet");
                throw new \Exception('Could not find sender wallet');
            }

            if($senderWallet->balance < $this->amount)
            {
                Log::error("Balance transfer error: Insufficient balance");
                throw new \Exception('Insufficient balance');
            }

            $receiverWallet->increment("balance", $this->amount);
            $senderWallet->decrement("balance", $this->amount);
            DB::commit();

        } catch(Throwable $e) {
            DB::rollback();
            Log::error("Balance transfer error ". $e->getMessage());
        }
    }
}
