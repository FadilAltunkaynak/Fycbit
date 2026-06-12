<?php

namespace App\Services\CacheService;

use App\Model\Buy;
use App\Model\Sell;
use Illuminate\Support\Str;

final class SpotOrderBookCacheService extends RedisManager
{
    public function __construct(){}
    private const ORDERBOOK_READY_TTL_SECONDS = 1800;

    private function summaryKey(int $tradeCoinId, int $baseCoinId): string
    {
        return "orderbook:summary:{$tradeCoinId}_{$baseCoinId}";
    }

    private function incrementTotalVolume(string $bookType, int $tradeCoinId, int $baseCoinId, string $amount, $pipe = null): void
    {
        if (bccomp((string) $amount, '0', 24) <= 0) {
            return;
        }

        $pipe ??= $this->redis();
        $pipe->hincrbyfloat(
            $this->summaryKey($tradeCoinId, $baseCoinId),
            "total_{$bookType}_amount",
            $amount
        );
    }

    private function decrementTotalVolume(string $bookType, int $tradeCoinId, int $baseCoinId, string $amount, $pipe = null): void
    {
        if (bccomp((string) $amount, '0', 24) <= 0) {
            return;
        }

        $pipe ??= $this->redis();
        $pipe->hincrbyfloat(
            $this->summaryKey($tradeCoinId, $baseCoinId),
            "total_{$bookType}_amount",
            bcmulx((string) $amount, '-1', 24)
        );
    }

    protected function lock(): mixed
    {
        return $this->redis()->set('orderbook:load:lock', 1, 'NX', 'EX', 300);
    }

    protected function unlock(): mixed
    {
        return $this->redis()->del('orderbook:load:lock');
    }

    protected function isLocked(): mixed
    {
        return $this->redis()->exists('orderbook:load:lock');
    }

    private function deleteOrderBookCache(): void
    {
        $keys = $this->redis()->keys('orderbook:*:*');
        if (!empty($keys)) {
            $this->redis()->del(...$keys);
        }
    }

    public function makeOrderBookReady()
    {
        $key = 'orderbook:ready';
        $isOrderReady = $this->cache()->has($key);
        if ($isOrderReady) {
            return;
        }

        $isLocked = $this->isLocked();
        if ($isLocked) {
            return;
        }

        $this->lock();

        try {
            $this->deleteOrderBookCache();

            Buy::where('status', 0)
            ->where('is_market', 0)
            ->chunkById(1000, fn($orders) => $this->loadOpenBookOrder('buy', $orders));

            Sell::where('status', 0)
            ->where('is_market', 0)
            ->chunkById(1000, fn($orders) => $this->loadOpenBookOrder('sell', $orders));

            $this->cache()->put($key, 1, self::ORDERBOOK_READY_TTL_SECONDS);
        } finally {
            $this->unlock();
        }
    }

    public function loadOpenBookOrder($bookType, $orders)
    {
        $this->redis()->pipeline(function($pipe) use($bookType, $orders) {
            foreach ($orders as $order) {
                $this->addOpenOrder($order, $bookType, $pipe);
            }
        });
    }

    public function addOpenOrder($order, $bookType, $pipe = null)
    {
        $pipe ??= $this->redis();
        $remaining = bcsubx((string) $order->amount, (string) $order->processed, 24);

        $pipe->zadd(
            "orderbook:{$bookType}:{$order->trade_coin_id}_{$order->base_coin_id}",
            trim($order->price),
            $order->id
        );

        $pipe->hset(
            "order:{$bookType}:{$order->id}",
            'id', $order->id,
            'price', trim($order->price),
            'amount', $order->amount,
            'user_id', $order->user_id,
            'processed', $order->processed,
            'is_bot', $order->is_bot,
            'base_coin_id', $order->base_coin_id,
            'trade_coin_id', $order->trade_coin_id,
            'maker_fees', $order->maker_fees,
            'taker_fees', $order->taker_fees,
            'created_at', $order->created_at->timestamp,
            'is_favorite', null,
            'percentage', 0
        );

        $this->incrementTotalVolume(
            $bookType,
            (int) $order->trade_coin_id,
            (int) $order->base_coin_id,
            $remaining,
            $pipe
        );
    }

    public function removeOpenOrder($order, ?string $bookType = null): void
    {
        $this->makeOrderBookReady();
        $bookType ??= strtolower(Str::singular($order->getTable()));
        if (! in_array($bookType, ['buy', 'sell'], true) || (int) $order->is_market === 1) {
            return;
        }

        $remaining = bcsubx((string) $order->amount, (string) $order->processed, 24);

        $this->redis()->pipeline(function ($pipe) use ($order, $bookType, $remaining) {
            $pipe->zrem(
                "orderbook:{$bookType}:{$order->trade_coin_id}_{$order->base_coin_id}",
                $order->id
            );
            $pipe->del("order:{$bookType}:{$order->id}");

            $this->decrementTotalVolume(
                $bookType,
                (int) $order->trade_coin_id,
                (int) $order->base_coin_id,
                $remaining,
                $pipe
            );
        });
    }

    public function removeOrderBookByPair(int $tradeCoinId, int $baseCoinId, string $type): void
    {
        $key = "orderbook:$type:{$tradeCoinId}_{$baseCoinId}";
        $summaryKey = $this->summaryKey($tradeCoinId, $baseCoinId);

        $orderIds = $this->redis()->zrange($key, 0, -1);

        $this->redis()->pipeline(function ($pipe) use ($key, $summaryKey, $orderIds, $type) {
            foreach ($orderIds as $orderId) {
                $pipe->del("order:$type:{$orderId}");
            }

            $pipe->del($key);
            $pipe->del($summaryKey);
        });
    }

    public function removeOrderBookByIds(array $orderIds, string $type): void
    {
        $this->redis()->pipeline(function ($pipe) use ($orderIds, $type) {
            $keys = $this->redis()->keys("orderbook:$type:*");
            foreach ($orderIds as $orderId) {
                foreach ($keys as $key) {
                    $removed = $pipe->zrem($key, $orderId);
                    if ($removed) {
                        $pipe->del("order:$type:{$orderId}");
                    }
                }
            }
        });
    }

    public function getOpenOrderIds($key, $min, $max, $sort = 'desc', $limit = 20)
    {
        // $key = "orderbook:{$oppositeOrderType}:{$tradeCoinId}_{$baseCoinId}";
        if ($sort == 'desc') {
            return $this->redis()->zrevrangebyscore($key, $max, $min, ['limit' => [0, $limit]]);
        } else {
            return $this->redis()->zrangebyscore($key, $min, $max, ['limit' => [0, $limit]]);
        }
    }

    public function getOrders($orderIds, $oppositeOrderType)
    {
        $this->makeOrderBookReady();
        $results = $this->redis()->pipeline(function ($pipe) use ($orderIds, $oppositeOrderType) {
            foreach ($orderIds as $orderId) {
                $pipe->hgetall("order:{$oppositeOrderType}:{$orderId}");
            }
        });

        $orders = [];

        foreach ($results as $data) {
            if (empty($data)) {
                continue;
            }

            $orders[] = (object) $data;
        }

        return $orders;
    }

    public function getLastOrderId($key, $orderIds): mixed
    {
        return $this->redis()->zscore($key, $orderIds);
    }

    public function openOrderUpdate($buy, $sell, $cmp, $orderType, $amountToBeProcessed)
    {
        $this->redis()->pipeline(function ($pipe) use ($buy, $sell, $cmp, $orderType, $amountToBeProcessed) {
            $sell_key = "order:sell:{$sell->id}";
            $buy_key = "order:buy:{$buy->id}";

            $buy_order_book_key = "orderbook:buy:{$buy->trade_coin_id}_{$buy->base_coin_id}";
            $sell_order_book_key = "orderbook:sell:{$sell->trade_coin_id}_{$sell->base_coin_id}";

            if ($buy->is_market == 0) {
                $this->decrementTotalVolume(
                    'buy',
                    (int) $buy->trade_coin_id,
                    (int) $buy->base_coin_id,
                    (string) $amountToBeProcessed,
                    $pipe
                );
            }

            if ($sell->is_market == 0) {
                $this->decrementTotalVolume(
                    'sell',
                    (int) $sell->trade_coin_id,
                    (int) $sell->base_coin_id,
                    (string) $amountToBeProcessed,
                    $pipe
                );
            }

            if ($cmp == -1) {
                if ($buy->is_market == 0 && $orderType == 'sell') {
                    $pipe->zrem($buy_order_book_key, $buy->id);
                    $pipe->del($buy_key);
                }
                if ($sell->is_market == 0 && $orderType == 'buy') {
                    $pipe->hincrbyfloat($sell_key, 'processed', $amountToBeProcessed);
                }
            }
            elseif ($cmp == 0) {
                if ($buy->is_market == 0 && $orderType == 'sell') {
                    $pipe->zrem($buy_order_book_key, $buy->id);
                    $pipe->del($buy_key);
                }
                if ($sell->is_market == 0 && $orderType == 'buy') {
                    $pipe->zrem($sell_order_book_key, $sell->id);
                    $pipe->del($sell_key);
                }
            }
            else {
                if ($buy->is_market == 0 && $orderType == 'sell') {
                    $pipe->hincrbyfloat($buy_key, 'processed', $amountToBeProcessed);
                }
                if ($sell->is_market == 0 && $orderType == 'buy') {
                    $pipe->zrem($sell_order_book_key, $sell->id);
                    $pipe->del($sell_key);
                }
            }
        });
    }

    public function getTotalVolume(int $baseCoinId, int $tradeCoinId): array
    {
        $this->makeOrderBookReady();
        $data = $this->redis()->hgetall($this->summaryKey($tradeCoinId, $baseCoinId));

        return [
            'total_buy_amount' => (string) ($data['total_buy_amount'] ?? '0'),
            'total_sell_amount' => (string) ($data['total_sell_amount'] ?? '0'),
        ];
    }

    public function getMaxMinPrice($trade_coin_id, $base_coin_id, $type)
    {
        $this->makeOrderBookReady();
        $key = "orderbook:{$type}:{$trade_coin_id}_{$base_coin_id}";
        $price = '0';

        if ($type == 'buy') {
            $maxPricedBuyOrder = $this->redis()->zrevrange($key, 0, 0, 'WITHSCORES');
            if (!empty($maxPricedBuyOrder)) {
                $price = array_values($maxPricedBuyOrder)[0];
            }
        }
        if ($type == 'sell') {
            $minPricedSellOrder = $this->redis()->zrange($key, 0, 0, 'WITHSCORES');
            if (!empty($minPricedSellOrder)) {
                $price = array_values($minPricedSellOrder)[0];
            }
        }

        return $price;
    }

    /**
     * Used in websocket data
     * @param mixed $trade_coin_id
     * @param mixed $base_coin_id
     * @param mixed $type
     * @return \Illuminate\Support\Collection
     */
    public function getOrdersData($base_coin_id, $trade_coin_id, $type)
    {
        $this->makeOrderBookReady();
        $key = "orderbook:{$type}:{$trade_coin_id}_{$base_coin_id}";
        $orderIds = ($type == 'buy') ? $this->redis()->zrevrange($key, 0, 49) : $this->redis()->zrange($key, 0, 49);

        $orders = $this->redis()->pipeline(function ($pipe) use ($orderIds, $type) {
            foreach ($orderIds as $orderId) {
                $pipe->hgetall("order:{$type}:{$orderId}");
            }
        });

        $depth = [];

        foreach ($orders as $order) {

            if (empty($order)) {
                continue;
            }

            $price = (string) $order['price'];
            $amount = (string) $order['amount'];
            $processed = (string) $order['processed'];

            $remaining = bcsub($amount, $processed, 24);

            if (bccomp($remaining, '0', 24) <= 0) {
                storeException('Negative amount - processed found.', $type . ' order id: ' . $order['id']);
                continue;
            }

            if (!isset($depth[$price])) {
                $depth[$price] = [
                    'amount' => '0',
                    'total' => '0',
                    'price' => '0',
                    'created_at' => ''
                ];
            }

            $depth[$price]['amount'] = bcadd($depth[$price]['amount'], $remaining, 24);
            $depth[$price]['price'] = $price;
            $depth[$price]['percentage'] =  $order['percentage'];
            if ($depth[$price]['created_at'] === '') {
                $depth[$price]['created_at'] = date('Y-m-d H:i:s', (int) $order['created_at']);
            }
        }

        if ($type == 'buy') {
            krsort($depth, SORT_NUMERIC);
        } else {
            ksort($depth, SORT_NUMERIC);
        }

        $result = [];

        foreach ($depth as $uniqueOrder) {


            $orderObj = (object) [
                'created_at' => $uniqueOrder['created_at'],
                'status' => 0,
                'processed' => '0.00000000',
                'price' => $uniqueOrder['price'],
                'amount' => $uniqueOrder['amount'],
                'total' => 0,
                'my_size' => 0,
                'is_favorite' => Null,
                'percentage' => $uniqueOrder['percentage'],
            ];

            $result[] = $orderObj;
        }
        return collect($result);
    }

    public function getPendingAmountSumByPrice(string $type, $price, $tradeCoinId, $baseCoinId): string
    {
        $this->makeOrderBookReady();
        $key = "orderbook:{$type}:{$tradeCoinId}_{$baseCoinId}";
        $orderIds = $this->redis()->zrangebyscore($key, $price, $price);

        if (empty($orderIds)) {
            return '0';
        }

        $orders = $this->getOrders($orderIds, $type);

        $totalSum = '0';
        foreach ($orders as $order) {
            $amount = $order->amount ?? '0';
            $processed = $order->processed ?? '0';
            $remaining = bcsubx((string) $amount, (string) $processed, 24);
            $totalSum = bcaddx($totalSum, $remaining, 24);
        }

        return $totalSum;
    }
}
