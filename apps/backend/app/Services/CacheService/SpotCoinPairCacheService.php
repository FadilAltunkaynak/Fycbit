<?php

namespace App\Services\CacheService;

use App\Http\Repositories\CoinPairRepository;
use App\Model\CoinPair;
use App\Model\Transaction;


final class SpotCoinPairCacheService extends RedisManager
{
    public function __construct(){}

    private function readyKey(): string
    {
        return 'coinpair:ready';
    }

    private function pairSetMember(int $childCoinId, int $parentCoinId): string
    {
        return "{$childCoinId}_{$parentCoinId}";
    }

    private function pairKey(int $childCoinId, int $parentCoinId): string
    {
        return "coinpair:{$this->pairSetMember($childCoinId, $parentCoinId)}";
    }

    private function syncReadyFlag(): void
    {
        $this->cache()->put($this->readyKey(), 1);
        $this->redis()->set($this->readyKey(), 1);
    }

    private function formatCoinPairPayload(CoinPair $coinPair): array
    {
        $coinPair->loadMissing(['child_coin', 'parent_coin']);

        return [
            "coin_pair_id", $coinPair->id,
            "coin_pair_name", $coinPair->child_coin->coin_type . '/' . $coinPair->parent_coin->coin_type,
            "coin_pair", $coinPair->child_coin->coin_type . '_' . $coinPair->parent_coin->coin_type,
            "parent_coin_id", $coinPair->parent_coin_id,
            "child_coin_id", $coinPair->child_coin_id,
            "last_price", $coinPair->price,
            "price_change", $coinPair->change,
            "child_coin_name", $coinPair->child_coin->coin_type,
            "icon", show_image_path($coinPair->child_coin->coin_icon, 'coin/'),
            "parent_coin_name", $coinPair->parent_coin->coin_type,
            "user_id", '',
            "balance", 0,
            "est_balance", 0,
            "is_favorite", 0,
            "high", $coinPair->high,
            "low", $coinPair->low,
            "volume", $coinPair->volume,
            "pair_name", $coinPair->child_coin->coin_type . '_' . $coinPair->parent_coin->coin_type,
            "bot_trading", $coinPair->bot_trading,
            "pair_decimal", $coinPair->pair_decimal,
            "child_full_name", $coinPair->child_coin->name,
            "parent_full_name", $coinPair->parent_coin->name,
            "initial_price", $coinPair->initial_price,
            "is_token", $coinPair->is_token,
        ];
    }

    protected function lock(): mixed
    {
        return $this->redis()->set('coinpair:load:lock', 1, 'NX', 'EX', 300);
    }

    protected function unlock(): mixed
    {
        return $this->redis()->del('coinpair:load:lock');
    }

    protected function isLocked(): mixed
    {
        return $this->redis()->exists('coinpair:load:lock');
    }

    public function storeLastPrice(Transaction $transaction)
    {
        $key = "coinpair:lastprice:{$transaction->trade_coin_id}_{$transaction->base_coin_id}";
        $this->redis()->set($key, $transaction->price);
    }

    public function makeCoinPairReady()
    {
        $key = $this->readyKey();
        $isCoinPairReady = $this->cache()->has($key);
        if ($isCoinPairReady) {
            return;
        }

        $isLocked = $this->isLocked();
        if ($isLocked) {
            return;
        }

        $this->lock();

        try {
            $coinPairRepository = app(CoinPairRepository::class);
            $pairs = $coinPairRepository->getAllCoinPairs();

            if (isset($pairs[0])) {
                $this->redis()->pipeline(function($pipe) use($pairs) {
                    foreach ($pairs as $pair) {
                        $pipe->sadd('active_coin_pair_list', "{$pair['child_coin_id']}_{$pair['parent_coin_id']}");
                        $coinPairKey = "coinpair:{$pair['child_coin_id']}_{$pair['parent_coin_id']}";

                        $pipe->hset(
                            $coinPairKey,
                            "coin_pair_id", $pair['id'],
                            "coin_pair_name", $pair['child_coin_name'] . '/' . $pair['parent_coin_name'],
                            "coin_pair", $pair['child_coin_name'] . '_' . $pair['parent_coin_name'],
                            "parent_coin_id", $pair['parent_coin_id'],
                            "child_coin_id", $pair['child_coin_id'],
                            "last_price", $pair['last_price'],
                            "price_change", $pair['price_change'],
                            "child_coin_name", $pair['child_coin_name'],
                            "icon", $pair['icon'],
                            "parent_coin_name", $pair['parent_coin_name'],
                            "user_id", $pair['user_id'] ?? '',
                            "balance", $pair['balance'] ?? 0,
                            "est_balance", $pair['est_balance'],
                            "is_favorite", $pair['is_favorite'],
                            "high", $pair['high'],
                            "low", $pair['low'],
                            "volume", $pair['volume'],
                            'pair_name', $pair['coin_pair_coin'],
                            'bot_trading', $pair['bot_trading'],
                            'pair_decimal', $pair['pair_decimal'],
                            'child_full_name', $pair['child_full_name'],
                            'parent_full_name', $pair['parent_full_name'],
                            'initial_price', $pair['initial_price'],
                            'is_token', $pair['is_token']
                        );
                    }
                });
            }

            $this->syncReadyFlag();
        } catch (\Exception $e) {
            storeException('get all coin pairs exception -> ', $e->getMessage());
        }

        $this->unlock();
    }

    public function getCoinPairs()
    {
        $response = [
            'status' => false,
            'message' => __('Data not found'),
            'data' => []
        ];
        try {

            if (! $this->cache()->has($this->readyKey()) && ! $this->redis()->exists($this->readyKey())) {
                $this->makeCoinPairReady();
            } 

            $coinPairTradeBaseIds = $this->redis()->smembers('active_coin_pair_list');

            $allPairs = $this->redis()->pipeline(function ($pipe) use ($coinPairTradeBaseIds) {
                foreach ($coinPairTradeBaseIds as $coinPairTradeBaseId) {
                    $pipe->hgetall("coinpair:{$coinPairTradeBaseId}");
                }
            });

            $response = [
                'status' => true,
                'message' => __('Data get successfully'),
                'data' => $allPairs
            ];

            return $response;
        } catch (\Exception $e) {
            storeException('get all coin pairs exception -> ', $e->getMessage());
            return $response;
        }
    }

    public function updateCoinPair24Data($pair, $transaction, $price24hData)
    {
        $this->redis()->hset(
            $this->pairKey($pair->child_coin_id, $pair->parent_coin_id),
            "last_price", $transaction->price,
            "price_change", $price24hData['change'] ?: $pair->change,
            "high", $price24hData['high'],
            "low", $price24hData['low'],
            "volume", $price24hData['volume']
        );
    }

    public function forgetCoinPair(int $childCoinId, int $parentCoinId): void
    {
        $pairSetMember = $this->pairSetMember($childCoinId, $parentCoinId);

        $this->redis()->pipeline(function ($pipe) use ($pairSetMember, $childCoinId, $parentCoinId) {
            $pipe->srem('active_coin_pair_list', $pairSetMember);
            $pipe->del($this->pairKey($childCoinId, $parentCoinId));
            $pipe->del("coinpair:lastprice:{$childCoinId}_{$parentCoinId}");
        });
    }

    public function syncCoinPair(CoinPair $coinPair, ?array $oldPair = null): void
    {
        if ($oldPair && (
            ($oldPair['child_coin_id'] ?? null) != $coinPair->child_coin_id ||
            ($oldPair['parent_coin_id'] ?? null) != $coinPair->parent_coin_id
        )) {
            $this->forgetCoinPair((int) $oldPair['child_coin_id'], (int) $oldPair['parent_coin_id']);
        }

        if ((int) $coinPair->status !== (int) STATUS_ACTIVE) {
            $this->forgetCoinPair($coinPair->child_coin_id, $coinPair->parent_coin_id);
            return;
        }

        $pairSetMember = $this->pairSetMember($coinPair->child_coin_id, $coinPair->parent_coin_id);
        $payload = $this->formatCoinPairPayload($coinPair);

        $this->redis()->pipeline(function ($pipe) use ($pairSetMember, $coinPair, $payload) {
            $pipe->sadd('active_coin_pair_list', $pairSetMember);
            $pipe->hset($this->pairKey($coinPair->child_coin_id, $coinPair->parent_coin_id), ...$payload);
        });

        $this->syncReadyFlag();
    }

    public function getLastPrice($request)
    {
        $this->makeCoinPairReady();

        $coinPairData = $this->redis()->hgetall("coinpair:{$request->trade_coin_id}_{$request->base_coin_id}");

        $output = [];
        if ($coinPairData) {
            $output = [
                [
                    "price" => $coinPairData['initial_price'],
                    "last_price" => $coinPairData['last_price']
                ]
            ];
        }
        return $output;
    }

    public function getCoinPair($tradeCoinId, $baseCoinId)
    {
        $this->makeCoinPairReady();

        return $this->redis()->hgetall("coinpair:{$tradeCoinId}_{$baseCoinId}");
    }


    public function storeMarketPrice($tradeCoinId, $baseCoinId, $price)
    {
        $this->cache()->put('coinpair:marketprice:{$tradeCoinId}_{$baseCoinId}', $price);
    }

    public function getMarketPrice($tradeCoinId, $baseCoinId)
    {
        return $this->cache()->get("coinpair:marketprice:{$tradeCoinId}_{$baseCoinId}");
    }
}
