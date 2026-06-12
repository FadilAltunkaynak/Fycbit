<?php

namespace Modules\FutureTrade\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Entities\FutureTrade;
use Modules\FutureTrade\Entities\FutureWallet;
use Modules\FutureTrade\Repositories\CoinPairRepository\CoinPairRepository;
use Modules\FutureTrade\Services\FuturePositionServices\FuturePositionService;
use Modules\FutureTrade\Services\OrderService\OrderService;

class FutureOpenPositionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private int $futureTradeId,
        private int $futureTradeBuyerId,
        private int $futureTradeSellerId,
        private int $coin_pair_id,
    ) {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(OrderService $orderService, FuturePositionService $positionService)
    {
        $futureTradeId = $this->futureTradeId;
        $futureTradeBuyerId = $this->futureTradeBuyerId;
        $futureTradeSellerId = $this->futureTradeSellerId;

        $coinPair = CoinPairRepository::getCoinPairById($this->coin_pair_id);

        if (!$coinPair) {
            errorLogger('6748: Coin Pair not found');

            return;
        }

        if ($futureTradeBuyerId === $futureTradeSellerId) {
            errorLogger('Buyer and seller are the same user, no action taken.');
            return;
        }

        $adminId = get_super_admin_id();
        if ($futureTradeBuyerId !== $adminId) {
            $buyerWallet = FutureWallet::where([
                'user_id' => $futureTradeBuyerId,
                'coin_id' => $coinPair->trade_coin_id
            ])->first();

            if (!$buyerWallet) {
                errorLogger('Buyer Wallet not found');

                return;
            }

            async(function() use($futureTradeId, $coinPair, $buyerWallet, $futureTradeBuyerId) {
                $positionService = app(FuturePositionService::class);
                $positionService->processPosition(
                    $futureTradeId,
                    $coinPair,
                    $buyerWallet,
                    $futureTradeBuyerId,
                    OrderType::BUY
                );
            });
        }

        if ($futureTradeSellerId !== $adminId) {
            $sellerWallet = FutureWallet::where([
                'user_id' => $futureTradeSellerId,
                'coin_id' => $coinPair->trade_coin_id
            ])->first();

            if (!$sellerWallet) {
                errorLogger('Seller Wallet not found');

                return;
            }

            async(function() use($futureTradeId, $coinPair, $sellerWallet, $futureTradeSellerId) {
                $positionService = app(FuturePositionService::class);
                $positionService->processPosition(
                    $futureTradeId,
                    $coinPair,
                    $sellerWallet,
                    $futureTradeSellerId,
                    OrderType::SELL
                );
            });
        }
    }
}
