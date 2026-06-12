<?php

namespace Modules\DemoTrade\Jobs;

use Illuminate\Bus\Queueable;
use Modules\DemoTrade\Entities\Buy;
use Modules\DemoTrade\Entities\Sell;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Modules\DemoTrade\Entities\UserWallet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\DemoTrade\Http\Repositories\UserWalletRepository;

class TransactionBroadcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param $buy
     * @param $sell
     * @param $transaction
     */
    public $buy;
    public $sell;
    public $transaction;
    public function __construct($buy, $sell, $transaction)
    {
        $this->buy = $buy;
        $this->sell = $sell;
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $newBuy = Buy::find($this->buy->id);
        $newSell = Sell::find($this->sell->id);
        !bccomp($newBuy->price, 0) ? $newBuy->price = $this->transaction->price : null;
        !bccomp($newSell->price, 0) ? $newSell->price = $this->transaction->price : null;

        broadcastOrderData($newBuy, 'buy', 'orderRemove', $newBuy->user_id);
        broadcastOrderData($newSell, 'sell', 'orderRemove', $newSell->user_id);
        $walletRepo = new UserWalletRepository(UserWallet::class);
        $sellerBaseWallet = $walletRepo->getDocs(['user_id' => $newSell->user_id, 'coin_id' => $newSell->base_coin_id])->first();
        $sellerTradeWallet = $walletRepo->getDocs(['user_id' => $newSell->user_id, 'coin_id' => $newSell->trade_coin_id])->first();
        $buyerBaseWallet = $walletRepo->getDocs(['user_id' => $newBuy->user_id, 'coin_id' => $newBuy->base_coin_id])->first();
        $buyerTradeWallet = $walletRepo->getDocs(['user_id' => $newBuy->user_id, 'coin_id' => $newBuy->trade_coin_id])->first();
        demoBroadcastWalletData($sellerBaseWallet->id, $newSell->user_id);
        demoBroadcastTransactionData($this->transaction);
        demoBroadcastWalletData($buyerBaseWallet->id, $newBuy->user_id);
        demoBroadcastWalletData($buyerTradeWallet->id, $newBuy->user_id);
        demoBroadcastWalletData($sellerTradeWallet->id, $newSell->user_id);
    }
}
