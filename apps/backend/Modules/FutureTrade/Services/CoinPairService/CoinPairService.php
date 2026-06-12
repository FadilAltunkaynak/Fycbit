<?php

namespace Modules\FutureTrade\Services\CoinPairService;

use App\Model\Coin;
use App\Model\CoinPair;
use Illuminate\Support\Str;
use Modules\FutureTrade\DataObject\FutureCoinPairData;
use Modules\FutureTrade\Entities\FutureCoinPair;
use Modules\FutureTrade\Entities\FutureLeverageSetting;
use Modules\FutureTrade\Http\Requests\FutureCoinPairRequest;
use Modules\FutureTrade\Repositories\CoinPairRepository\CoinPairRepository;
use Modules\FutureTrade\Repositories\CoinPairRepository\ICoinPairRepository;
use Modules\FutureTrade\Services\CoinPairService\Traits\CoinPairGeneralCheck;
use Modules\FutureTrade\Services\CoinPairService\Traits\CoinPairTradeCheck;
use Modules\FutureTrade\Transformers\FutureCoinPairApiList;
use Ramsey\Uuid\Type\Hexadecimal;

class CoinPairService
{
    use CoinPairGeneralCheck, CoinPairTradeCheck;

    public CoinPairRepository $repository;

    public function __construct(ICoinPairRepository $repository)
    {
        $this->repository = $repository;
    }

    public function storeFutureCoinPair(FutureCoinPairRequest $request)
    {
        $coinPair = null;
        $hasUid = (bool) $request?->coin_pair_id;
        $dataArray = $this->getDataObject($request);

        if ($hasUid) {
            $coinPair = CoinPairRepository::getCoinPairByUid($request->coin_pair_id);
            if (! $coinPair) {
                return failed(__('Coin pair not found'));
            }

            // Remove base and trade coin code if present in request
            $dataArray->setProperty('base_coin_code', '', true);
            $dataArray->setProperty('trade_coin_code', '', true);
        } else {
            $baseCoin = $this->getBaseCoin($request->base_coin_code);
            $tradeCoin = $this->getTradeCoin($request->trade_coin_code);
            $sameCoins = $this->verifyBaseAndTradeCoinNotSame($baseCoin, $tradeCoin);
            $sameCoins = $this->hasCoinPair($baseCoin, $tradeCoin);

            $dataArray->setProperty('uid', $this->getNewUuid());
            $dataArray->setProperty('base_coin_id', $baseCoin->id);
            $dataArray->setProperty('trade_coin_id', $tradeCoin->id);
            $dataArray->setProperty('code', "{$request->base_coin_code}{$request->trade_coin_code}");
        }

        $response = match (true) {
            $hasUid => $this->repository->updateCoinPair($coinPair, $dataArray),
            default => $this->repository->insertCoinPair($dataArray)
        };

        if ($response) {
            return success(__('Coin pair saved successfully.'));
        }

        return failed(__('Coin pair failed to save.'));
    }

    public function deleteCoinPair(string $uid)
    {
        $coinPair = CoinPairRepository::getCoinPairByUid($uid);
        if (! $coinPair) {
            return failed(__('Coin pair not found'));
        }

        $defaultCoinPair = $this->isDefaultCoinPair($coinPair);
        if ($defaultCoinPair) {
            return failed(__('This coin pair is default coin pair, so you cannot delete this pair.'));
        }

        $hasOrder = $this->hasOpenAnyOrder($coinPair);
        if ($hasOrder) {
            return failed(__('This coin pair already have some open orders, so you should not delete this pair.'));
        }

        $hasPosition = $this->hasOpenAnyPosition();
        if ($hasPosition) {
            return failed(__('This coin pair already have open position, so you should not delete this pair.'));
        }

        $deleted = $coinPair->delete();
        if (! $deleted) {
            return failed(__('Coin pair not deleted'));
        }

        return success(__('Coin pair deleted successfully'));
    }

    public function updateCoinPairStatus(string $uid)
    {
        $coinPair = CoinPairRepository::getCoinPairByUid($uid);
        if (! $coinPair) {
            return failed(__('Coin pair not found'));
        }

        $leverageSettings = FutureLeverageSetting::where('coin_pair_uid', $coinPair->uid)->exists();
        if (! $leverageSettings) {
            return failed(__('Add leverage settings to enable this coin pair.'));
        }

        $updated = $coinPair->update(['status' => ! $coinPair?->status?->value]);

        if (! $updated) {
            return failed(__('Coin pair status update failed.'));
        }

        return success(__('Coin pair status updated successfully.'));
    }

    public function getNewUuid(): Hexadecimal
    {
        return Str::uuid()->getHex();
    }

    public function getDataObject(FutureCoinPairRequest $request): FutureCoinPairData
    {
        $data = new FutureCoinPairData(
            $request->collateral_type,
            $request->base_coin_code ?? '',
            $request->trade_coin_code ?? '',
            $request->base_decimal,
            $request->trade_decimal,
        );
        $data->setProperty('maker_fees_percent', $request->maker_fees_percent);
        $data->setProperty('taker_fees_percent', $request->taker_fees_percent);
        $data->setProperty('slippage_percent', $request->slippage_percent, true);
        $data->setProperty('max_open_orders', $request->max_open_orders);
        $data->setProperty('min_stop_limit_percent', $request->min_stop_limit_percent);
        $data->setProperty('max_stop_limit_percent', $request->max_stop_limit_percent);
        $data->setProperty('max_amount', $request->max_amount);
        $data->setProperty('min_amount', $request->min_amount);
        $data->setProperty('max_leverage', $request->max_leverage);
        $data->setProperty('funding_rate', $request->funding_rate);
        $data->setProperty('cap_ratio', $request->cap_ratio);
        $data->setProperty('floor_ratio', $request->floor_ratio);
        $data->setProperty('status', $request->status, true);

        return $data;
    }

    /**
     * Get All Active Coin Pairs
     */
    public function getCoinPairList(?string $limit = null): array
    {
        $coinPairs = $this->repository->getCoinPairList($limit);
        return success(FutureCoinPairApiList::collection($coinPairs));
    }

    public function coinPairMarkIndexUpdate()
    {
        FutureCoinPair::statusActive()->chunk(100, function ($pairs) {
            foreach ($pairs as $pair) {
                $spotCoinPair = CoinPair::where('parent_coin_id', $pair->base_coin_id)
                    ->where('child_coin_id', $pair->trade_coin_id)
                    ->first();

                $index_price = $spotCoinPair->price ?? 0;
                $mark_price = $index_price;
            }
        });
    }
}
