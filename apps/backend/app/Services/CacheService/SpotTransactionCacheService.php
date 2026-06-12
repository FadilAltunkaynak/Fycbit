<?php

namespace App\Services\CacheService;

use App\Model\Transaction;
use Illuminate\Support\Facades\DB;

final class SpotTransactionCacheService extends RedisManager
{
    public function __construct(){}

    protected function lock(): mixed
    {
        return $this->redis()->set('transaction:load:lock', 1, 'NX', 'EX', 300);
    }

    protected function unlock(): mixed
    {
        return $this->redis()->del('transaction:load:lock');
    }

    protected function isLocked(): mixed
    {
        return $this->redis()->exists('transaction:load:lock');
    }

    public function makeTransactionReady($trade_coin_id, $base_coin_id)
    {
        $key = "transactions:ready:{$trade_coin_id}_{$base_coin_id}";
        $isTransactionsReady = $this->cache()->has($key);
        if ($isTransactionsReady) {
            return;
        }

        $isLocked = $this->isLocked();
        if ($isLocked) {
            return;
        }

        $this->lock();
        try {
            $transactions = Transaction::select(
                DB::raw("visualNumberFormat(amount) as amount"),
                DB::raw("visualNumberFormat(price) as price"),
                DB::raw("visualNumberFormat(last_price) as last_price"),
                DB::raw("visualNumberFormat(total) as total"),
                'price_order_type',
                'created_at',
            )->where([
                'base_coin_id' => $base_coin_id,
                'trade_coin_id' => $trade_coin_id,
            ])->orderBy('id', 'DESC')->limit(50)->get();

            $transactionsKey = "transactions:{$trade_coin_id}_{$base_coin_id}";

            $data = $transactions->map(function ($t) {
                return json_encode([
                    'amount' => $t->amount,
                    'price' => $t->price,
                    'last_price' => $t->last_price,
                    'total' => $t->total,
                    'price_order_type' => $t->price_order_type,
                    'time' => $t->created_at->toDateTimeString(),
                ]);
            })->toArray();

            $this->redis()->del($transactionsKey);

            if (!empty($data)) {
                $this->redis()->rpush($transactionsKey, ...$data);
            }
        } catch (\Exception $e) {
            storeException('get transactions exception -> ', $e->getMessage());
        }

        $this->cache()->put($key, 1);
        $this->unlock();
    }

    public function addTransaction(Transaction $transaction)
    {
        $transKey = "transactions:{$transaction->trade_coin_id}_{$transaction->base_coin_id}";

        $jsonData = json_encode([
                'base_coin_id' => $transaction->base_coin_id,
                'trade_coin_id' => $transaction->trade_coin_id,
                'amount' => $transaction->amount,
                'price' => $transaction->price,
                'last_price' => $transaction->last_price,
                'total' => $transaction->total,
                'price_order_type' => $transaction->price_order_type,
                'time' => $transaction->created_at->toDateTimeString(),
        ]);

        $this->redis()->pipeline(function ($pipe) use ($transKey, $jsonData, $transaction) {
            $pipe->lpush($transKey, $jsonData);
            $pipe->ltrim($transKey, 0, 49);

            app('spot_coinpair_cache')->storeMarketPrice(
                $transaction->trade_coin_id,
                $transaction->base_coin_id,
                $transaction->price
            );
        });
    }

    public function getTransactions($request)
    {
        try {
            $this->makeTransactionReady($request->base_coin_id, $request->trade_coin_id);

            $key = "transactions:{$request->trade_coin_id}_{$request->base_coin_id}";
            $rawHistory = $this->redis()->lrange($key, 0, -1);
            $data['transactions'] = array_map('json_decode', $rawHistory);

            return success( $data);
        } catch (\Exception $e) {
            storeLog(processExceptionMsg($e), "error");
            return failed();
        }
    }

    public function getLastTransaction($request)
    {
        try {
            $this->makeTransactionReady($request->base_coin_id, $request->trade_coin_id);

            $key = "transactions:{$request->trade_coin_id}_{$request->base_coin_id}";
            $latestJson = $this->redis()->lindex($key, 0);
            $latest = json_decode($latestJson, true);

            return success($latest);
        } catch (\Exception $e) {
            storeException('get getMarketLastTransactions history exception -> ', $e->getMessage());
            return failed();
        }
    }

    public function getLastPrice($request)
    {
        $this->makeTransactionReady($request->base_coin_id, $request->trade_coin_id);

        $key = "transactions:{$request->trade_coin_id}_{$request->base_coin_id}";
        $latestJson = $this->redis()->lindex($key, 0);
        $latest = json_decode($latestJson, true);

        $output = [];
        if ($latest) {
            $output = [
                [
                    "price" => $latest['price'],
                    "last_price" => $latest['last_price'],
                ]
            ];
            return $output;
        }

        return [];
    }
}