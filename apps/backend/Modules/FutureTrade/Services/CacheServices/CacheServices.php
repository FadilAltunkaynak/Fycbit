<?php

namespace Modules\FutureTrade\Services\CacheServices;

use App\Model\Coin;
use App\Model\Transaction;
use Illuminate\Support\Facades\Redis;
use Modules\FutureTrade\DataObject\OrderCacheAbleData;
use Modules\FutureTrade\Emum\OrderMethod;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Entities\FutureCoinPair;
use Modules\FutureTrade\Entities\FutureTrade;
use Modules\FutureTrade\Entities\FuturePrice;
use Modules\FutureTrade\Repositories\OrderRepository\IOrderRepository;

class CacheServices
{
    private $ignore_db_load_check = false;
    public function setMarkPrice($pair_id, $mark_price)
    {
        $key = "mark_price_coin_pair_$pair_id";
        Redis::connection('future_trade_cache')->set($key, $mark_price);

        FuturePrice::where('future_coin_pair_id', $pair_id)->update([
            'mark_price' => $mark_price
        ]);
    }

    public function getMarkPrice($pair_id): mixed
    {
        $key = "mark_price_coin_pair_$pair_id";
        $mark_price = Redis::connection('future_trade_cache')->get($key);

        if(! $mark_price) {
            // Check FuturePrice table first
            $futurePrice = FuturePrice::where('future_coin_pair_id', $pair_id)->value('mark_price');
            if ($futurePrice) {
                $mark_price = $futurePrice;
                $this->setMarkPrice($pair_id, $mark_price);
                return $mark_price;
            }

            // Fallback to Transaction table
            $futureCoinPair = FutureCoinPair::find($pair_id);
            $mark_price = Transaction::where('base_coin_id', $futureCoinPair->trade_coin_id)
                ->where('trade_coin_id', $futureCoinPair->base_coin_id)
                ->orderBy('created_at', 'desc')
                ->value('price');
            if (! $mark_price) {
                $mark_price = Coin::where('id', $futureCoinPair->base_coin_id)->value('coin_price');
            }
            $mark_price ??= 0;
            $this->setMarkPrice($pair_id, $mark_price);
        }

        return $mark_price;
    }
    public function setIndexPrice($pair_id, $index_price)
    {
        $key = "index_price_coin_pair_$pair_id";
        Redis::connection('future_trade_cache')->set($key, $index_price);

        FuturePrice::where('future_coin_pair_id', $pair_id)->update([
            'index_price' => $index_price
        ]);
    }

    public function getIndexPrice($pair_id): mixed
    {
        $key = "index_price_coin_pair_$pair_id";
        $index_price = Redis::connection('future_trade_cache')->get($key);

        if(! $index_price) {
            // Check FuturePrice table first
            $futurePrice = FuturePrice::where('future_coin_pair_id', $pair_id)->value('index_price');
            if ($futurePrice) {
                $index_price = $futurePrice;
                $this->setIndexPrice($pair_id, $index_price);
                return $index_price;
            }

            // Fallback to Transaction table
            $futureCoinPair = FutureCoinPair::find($pair_id);
            $index_price = Transaction::where('base_coin_id', $futureCoinPair->trade_coin_id)
                ->where('trade_coin_id', $futureCoinPair->base_coin_id)
                ->orderBy('created_at', 'desc')
                ->value('price');
            if (! $index_price) {
                $index_price = Coin::where('id', $futureCoinPair->base_coin_id)->value('coin_price');
            }
            $index_price ??= 0;
            $this->setIndexPrice($pair_id, $index_price);
        }

        return $index_price;
    }
    
    public function setMarketPrice($pair_id, $market_price)
    {
        $key = "market_price_coin_pair_$pair_id";
        Redis::connection('future_trade_cache')->set($key, $market_price);

        FuturePrice::where('future_coin_pair_id', $pair_id)->update([
            'market_price' => $market_price
        ]);
    }

    public function getMarketPrice($pair_id): mixed
    {
        $key = "market_price_coin_pair_$pair_id";
        $market_price = Redis::connection('future_trade_cache')->get($key);

        if(! $market_price) {
            // Check FuturePrice table first
            $futurePrice = FuturePrice::where('future_coin_pair_id', $pair_id)->value('market_price');
            if ($futurePrice) {
                $market_price = $futurePrice;
                $this->setMarketPrice($pair_id, $market_price);
                return $market_price;
            }

            // Fallback to FutureTrade table
            $market_price = FutureTrade::where('future_coin_pair_id', $pair_id)
                ->orderBy('created_at', 'desc')
                ->value('price');
            $market_price ??= 0;

            if(! $market_price) {
                $futureCoinPair = FutureCoinPair::find($pair_id);
                $market_price = convert_currency(1, $futureCoinPair->trade_coin_code, $futureCoinPair->base_coin_code);
            }
            $this->setMarketPrice($pair_id, $market_price);
        }

        return $market_price;
    }

    /**
     * Check if redis has no data
     * then fill data from db
     */
    public function db_load(int $coinPairId): void
    {
        if ($this->ignore_db_load_check) {
            return;
        }

        $has = Redis::connection('future_trade_cache')->exists("future-orderbook-$coinPairId:bids")
               || Redis::connection('future_trade_cache')->exists("future-orderbook-$coinPairId:asks");

        if (! $has) {
            $this->ignore_db_load_check = true;
            /** @var IOrderRepository $repository */
            $repository = app('future-order-repository');

            array_map(
                function ($orderType) use ($coinPairId, $repository) {
                    $orders = $repository->getOrdersRecursive(
                        coinPair: $coinPairId,
                        orderType: $orderType,
                        orderMethod: OrderMethod::LIMIT
                    );

                    while ($orders) {
                        $orderList = $orders->current();
                        foreach ($orderList as $order) {
                            $this->addOrder(
                                OrderCacheAbleData::fromOrder($order)
                            );
                        }
                        if (is_null($orders->next())) {
                            break;
                        }
                    }
                },
                [OrderType::BUY, OrderType::SELL]
            );
            $this->ignore_db_load_check = false;
        }
    }

    /**
     * Check if redis has data
     */
    public function clean_db(): bool
    {
        return (bool) Redis::connection('future_trade_cache')->flushdb();
    }

    /**
     * add an order in cache
     */
    public function addOrder(OrderCacheAbleData $data): bool
    {
        $bookKey = match ($data->order_type) {
            OrderType::BUY => "future-orderbook-$data->coin_pair_id:bids",
            OrderType::SELL => "future-orderbook-$data->coin_pair_id:asks"
        };

        $orderPrice = match ($data->order_type) {
            OrderType::BUY => $this->getBidByPrice($data->coin_pair_id, $data->price),
            OrderType::SELL => $this->getAskByPrice($data->coin_pair_id, $data->price)
        };

        if ($orderPrice) {
            $order = $this->getOrder($data->coin_pair_id, $data->order_type, ($orderPrice[0] ?? 0));

            return $this->addAmountToExistingOrder($data->coin_pair_id, $data->order_type, $order['price'], $data->amount);
        }

        Redis::connection('future_trade_cache')->zadd($bookKey, $data->price, $data->price);
        Redis::connection('future_trade_cache')->hmset("order-$data->coin_pair_id-{$data->order_type->value}:$data->price", $data->toArray());

        return true;
    }

    /**
     * get Best Buy Order
     */
    public function bestBid(int $coinPairId)
    {
        $bids = Redis::connection('future_trade_cache')->zRevRange("future-orderbook-$coinPairId:bids", '0', '-1');

        return $bids ? $bids : null;
    }

    public function addAmountToExistingOrder(int $coinPairId, OrderType $orderType, float|string $orderPrice, float|string $amount)
    {
        return Redis::connection('future_trade_cache')
            ->command('HINCRBYFLOAT', [
                "order-$coinPairId-$orderType->value:$orderPrice",
                'amount', $amount,
            ]);
    }

    /**
     * Get Best Sell
     */
    public function bestAsk(int $coinPairId)
    {
        $this->db_load($coinPairId);
        $asks = Redis::connection('future_trade_cache')->command(
            'zrange',
            ["future-orderbook-$coinPairId:asks", 0, -1]
        );

        return $asks ? $asks : null;
    }

    /**
     * get Buy price from cache
     */
    public function getAskByPrice(int $coinPairId, float|string $price)
    {
        $asks = Redis::connection('future_trade_cache')->command(
            'zrangebyscore',
            ["future-orderbook-$coinPairId:asks", $price, $price]
        );

        return $asks ? $asks : null;
    }

    /**
     * get sell price from cache
     */
    public function getBidByPrice(int $coinPairId, float|string $price)
    {
        $bids = Redis::connection('future_trade_cache')->command(
            'zrangebyscore',
            ["future-orderbook-$coinPairId:bids", $price, $price]
        );

        if (! $bids) {
            return null;
        }

        return $bids ? $bids : null;
    }

    public function getPrices(int $coinPairId, OrderType $orderType, float|string $price, float|string|null $stop_price = null): array
    {
        $bookKey = match ($orderType) {
            OrderType::BUY => "future-orderbook-$coinPairId:bids",
            OrderType::SELL => "future-orderbook-$coinPairId:asks"
        };

        $params = match ($orderType) {
            OrderType::BUY => [$bookKey, $price, $stop_price ?: $price,"REV", "BYSCORE"],
            OrderType::SELL => [$bookKey, $price, $stop_price ?: $price, "BYSCORE"],
        };

        $prices = Redis::connection('future_trade_cache')->command('zrange', $params);

        return $prices ? $prices : [];
    }

    /**
     * get an order from cache
     */
    public function getOrder(int $coinPairId, OrderType $orderType, float|string $orderPrice): array
    {
        $this->db_load($coinPairId);

        return Redis::connection('future_trade_cache')
            ->command('hgetall', ["order-$coinPairId-$orderType->value:$orderPrice"]);
    }

    /**
     * Get all orders from cache
     */
    public function getAllOrders(int $coinPairId, ?OrderType $orderType = null): array
    {

        if (! $orderType) {
            return [
                'buy' => $this->getAllOrders($coinPairId, OrderType::BUY),
                'sell' => $this->getAllOrders($coinPairId, OrderType::SELL),
            ];
        }

        $this->db_load($coinPairId);
        $prefix = config('database.redis.options.prefix', '');
        $keys = Redis::connection('future_trade_cache')->scan(0, 'MATCH', $prefix."order-{$coinPairId}-{$orderType->value}:*");

        $data = Redis::connection('future_trade_cache')
            ->pipeline(function ($pipe) use ($keys, $prefix) {
                foreach (($keys[1] ?? []) as $k) {
                    $k = str_replace($prefix, '', $k);
                    $pipe->hgetall($k);
                }
            });

        return $data;
    }

    /**
     * remove an Order from cache
     */
    public function removeOrder(int $coinPairId, OrderType $orderType, float|string $orderPrice): bool
    {
        $this->db_load($coinPairId);
        $details = Redis::connection('future_trade_cache')->command('hgetall', ["order-$coinPairId-$orderType->value:$orderPrice"]);

        if (! $details) {
            return false;
        }

        $bookKey = match ($orderType) {
            OrderType::BUY => "future-orderbook-$coinPairId:bids",
            OrderType::SELL => "future-orderbook-$coinPairId:asks"
        };

        Redis::connection('future_trade_cache')->command('zrem', [$bookKey, $orderPrice]);
        Redis::connection('future_trade_cache')->command('del', ["order-$coinPairId-$orderType->value:$orderPrice"]);

        return true;
    }

    /**
     * Update future market price
     */
    public static function updateFutureMarketPrice(int $pairId, float|string $price)
    {
        Redis::connection('future_trade_cache')->set("future_market_price-$pairId", $price);
    }

    /**
     * Get future market price
     */
    public static function getFutureMarketPrice(int $pairId): float|string
    {
        return Redis::connection('future_trade_cache')->get("future_market_price-$pairId");
    }
}
