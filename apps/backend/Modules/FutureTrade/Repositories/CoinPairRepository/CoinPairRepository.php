<?php

namespace Modules\FutureTrade\Repositories\CoinPairRepository;

use App\Http\Repositories\CommonRepository;
use Illuminate\Support\Collection;
use Modules\FutureTrade\DataObject\FutureCoinPairData;
use Modules\FutureTrade\Entities\FutureCoinPair;

class CoinPairRepository extends CommonRepository implements ICoinPairRepository
{
    public function __construct(?FutureCoinPair $model = null)
    {
        parent::__construct($model ?: app(FutureCoinPair::class));
    }

    /**
     * Get Coin Pair By UID
     */
    public static function getCoinPairByUid(string $uid): ?FutureCoinPair
    {
        return FutureCoinPair::where('uid', $uid)->first();
    }

    /**
     * Get Coin Pair By ID
     */
    public static function getCoinPairById($id): ?FutureCoinPair
    {
        return FutureCoinPair::find($id);
    }

    /**
     * Get Coin Pair By CODE
     */
    public static function getCoinPairByCode($code): ?FutureCoinPair
    {
        return FutureCoinPair::where('code', operator: $code)->first();
    }

    /**
     * Insert New Coin Pair Data
     */
    public function insertCoinPair(FutureCoinPairData $data): ?FutureCoinPair
    {
        try {
            return FutureCoinPair::create($data->toArray());
        } catch (\Throwable $th) {
            storeException('futureCreateCoinPair', $th->getLine());
            storeException('futureCreateCoinPair', $th->getMessage());

            return null;
        }
    }

    /**
     * Update Coin Pair
     */
    public function updateCoinPair(FutureCoinPair $coinPair, FutureCoinPairData $data): ?FutureCoinPair
    {
        try {
            $update = $coinPair->update($data->toArray());
            if ($update) {
                return $coinPair;
            }

            return null;
        } catch (\Throwable $th) {
            storeException('futureUpdateCoinPair', $th->getLine());
            storeException('futureUpdateCoinPair', $th->getMessage());

            return null;
        }
    }

    /**
     * Get All Coin Pairs
     *
     * @return Collection<?FutureCoinPair>
     */
    public function getCoinPairList(?string $limit = null): Collection
    {
        // TODO FIL: may be paginate is better here
        return $this->model->statusActive()->select(
            'id','uid', 'code', 'trade_decimal', 'base_decimal', 'trade_coin_id', 'base_coin_id', 'trade_coin_code', 'base_coin_code', 'status',
            'min_amount', 'max_amount', 'min_stop_limit_percent', 'max_stop_limit_percent',
            'maker_fees_percent', 'taker_fees_percent', 'max_leverage', 'cap_ratio', 'floor_ratio'
        )->limit($limit ?: 20)->get();
    }
}
