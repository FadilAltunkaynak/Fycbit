<?php

namespace App\Services\TradingBotServices;

use App\Cache\BotCoinPairCache;
use App\Contracts\Repositories\BotCoinPairRepositoryInterface;
use App\Contracts\Repositories\TradeTransactionRepositoryInterface;
use App\Dtos\Calculate24HourPriceDto;
use App\Http\Repositories\BuyOrderRepository;
use App\Http\Repositories\SellOrderRepository;
use App\Http\Repositories\StopLimitRepository;
use App\Http\Services\BuySellTransactionService;
use App\Http\Services\CacheService;
use App\Http\Services\TradeServices\TransactionDataFethcerService;
use App\Http\Services\TransactionService;
use App\Jobs\BotOrderJob;
use App\Jobs\StopLimitProcessJob;
use App\Jobs\TradingViewChartJob;
use App\Model\Buy;
use App\Model\CoinPair;
use App\Model\Sell;
use App\Model\StopLimit;
use App\Model\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BotOrderService
{

    private $amountToBeProcessed = 0;
    private $buyAbleAmount = 0;
    private $sellAbleAmount = 0;
    public function __construct(
        private TransactionDataFethcerService $transactionDataFethcerService,
        private TradeTransactionRepositoryInterface $tradeTransactionRepository,
        private BotCoinPairRepositoryInterface $coinPairRepository,
        private CacheService $cacheService,
        private BotCoinPairCache $botCoinPairCache
    ) {}

    public function getCoinPairsAndProcess(int $adminId): void
    {
        $coinPairs = $this->botCoinPairCache->getBotStatusActivePairs();

        foreach ($coinPairs as $coinPair) {
            $intervalInSec = $this->resolveBotInterval($coinPair);

            if ($intervalInSec === null) {
                continue;
            }

            $startTime = $this->cacheService->get('bot_order_place_time_for_coin_pair_'.$coinPair->id);

            if ($startTime && ! $this->checkBotOrderPlacingInterval($startTime, $intervalInSec)) {
                continue;
            }

            if (! $this->cacheService->get('bot_order_status_'.$coinPair->id)) {
                $this->cacheService->setWithTimeOut('bot_order_status_'.$coinPair->id, 'processing', 60);
                BotOrderJob::dispatch($coinPair, $adminId)->onQueue('bot-order');
            }
        }
    }

    private function resolveBotInterval($coinPair): ?int
    {
        if (!empty($coinPair->bot_interval)) {
            return (int) $coinPair->bot_interval;
        }

        $defaultInterval = settings('trading_bot_buy_interval');

        if ($defaultInterval === null || $defaultInterval === '') {
            return null;
        }

        $defaultInterval = (int) $defaultInterval;

        return $defaultInterval > 0 ? $defaultInterval : null;
    }

    private function checkBotOrderPlacingInterval(Carbon $start, int $intervalInSec): bool
    {
        $end = Carbon::now();
        $differenceInSeconds = $end->diffInSeconds($start);

        return $differenceInSeconds >= $intervalInSec;
    }

    public function order($order, $beingProcessingOrder, $orderType)
    {
        DB::beginTransaction();
        try {
            $total = "visualNumberFormat(TRUNCATE(sum((amount - processed) * price), 8)) as total";
            $buyAbleAmount = "amount - processed as buyAbleAmount";
            $sellAbleAmount = "amount - processed as sellAbleAmount";

            if ($orderType == 'buy') {
                $buy = Buy::selectRaw("$total, $buyAbleAmount, buys.*")->lockForUpdate()->where("id", $order->id)->first();
                $sell = Sell::selectRaw("$total, $sellAbleAmount, sells.*")->lockForUpdate()->where("id", $beingProcessingOrder)->first();
            } else {
                $buy = Buy::selectRaw("$total, $buyAbleAmount, buys.*")->lockForUpdate()->where("id", $beingProcessingOrder)->first();
                $sell = Sell::selectRaw("$total, $sellAbleAmount, sells.*")->lockForUpdate()->where("id", $order->id)->first();
            }

            if (!$buy || !$sell) {
                DB::rollBack();
                return false;
            }

            $this->sellAbleAmount = $sell->sellAbleAmount;
            $this->buyAbleAmount  = $buy->buyAbleAmount;


            if (bccompx($this->buyAbleAmount, "0") === 0 || bccompx($this->sellAbleAmount, "0") === 0) {
                // Extra check if any Available Amount 0
                DB::rollBack();
                return true;
            }


            if (bccompx($this->buyAbleAmount, $this->sellAbleAmount) != 1) {
                $this->amountToBeProcessed = $this->buyAbleAmount;
            } else {
                $this->amountToBeProcessed = $this->sellAbleAmount;
            }


            $input = $this->transactionDataFethcerService
                ->fetchTransactionData($buy, $sell, $this->amountToBeProcessed);


            $cmp = bccompx($this->buyAbleAmount, $this->sellAbleAmount);
            if ( $cmp == -1) {
                $buy->increment('processed', $this->amountToBeProcessed, ['status' => 1]);
                $sell->increment('processed', $this->amountToBeProcessed);
            } else if ($cmp == 0) {
                $sell->increment('processed', $this->amountToBeProcessed, ['status' => 1]);
                $buy->increment('processed', $this->amountToBeProcessed, ['status' => 1]);
            } else if ($cmp == 1) {
                $buy->increment('processed', $this->amountToBeProcessed);
                $sell->increment('processed', $this->amountToBeProcessed, ['status' => 1]);
            }

            $transaction = $this->tradeTransactionRepository->create($input);

            $transactionId = isset($transactionId) ? $transactionId : time() . fixedlenstr($transaction->id);
            Transaction::where(['id' => $transaction->id])->update(['transaction_id' => $transactionId]);

            $buySellService = app(BuySellTransactionService::class);
            $buySellService->update24HourPrice($transaction);
            $buySellService->_checkConditionedOrders($transaction);

            DB::commit();

            app('spot_orderbook_cache')->openOrderUpdate($buy, $sell, $cmp, $orderType, $this->amountToBeProcessed);
            app('spot_transaction_cache')->makeTransactionReady($buy->trade_coin_id, $buy->base_coin_id);
            app('spot_transaction_cache')->addTransaction($transaction);
            app('spot_coinpair_cache')->storeLastPrice($transaction);

            dispatch(new TradingViewChartJob($transaction));

            if (($orderType == 'buy' && $buy->status == 1) || ($orderType == 'sell' && $sell->status == 1)) {
                return false;
            }
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return true;
        }
    }
}
