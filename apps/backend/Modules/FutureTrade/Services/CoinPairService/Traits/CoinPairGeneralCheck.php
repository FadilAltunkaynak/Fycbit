<?php
namespace Modules\FutureTrade\Services\CoinPairService\Traits;

use App\Facades\ResponseFacade;
use App\Model\Coin;
use App\Model\AdminSetting;
use Modules\FutureTrade\Entities\FutureCoinPair;

trait CoinPairGeneralCheck
{
    /**
     * Check if Base coin exists
     * @throws \Exception
     */
    public function getBaseCoin(string $baseCoinCode): Coin
    {
        $coin = Coin::where('coin_type', $baseCoinCode)->first();
        if(!$coin) ResponseFacade::failed(__("Base coin not found"))->safeThrow();
        return $coin;
    }

    /**
     * Check if Trade coin exists
     * @throws \Exception
     */
    public function getTradeCoin(string $tradeCoinCode): Coin
    {
        $coin = Coin::where('coin_type', $tradeCoinCode)->first();
        if(!$coin) ResponseFacade::failed(__("Trade coin not found"))->safeThrow();
        return $coin;
    }

    /**
     * Check if selected coins are same
     * @throws \Exception
     */
    public function verifyBaseAndTradeCoinNotSame(Coin $baseCoin, Coin $tradeCoin): bool
    {
        $same = $baseCoin->id == $tradeCoin->id;
        if($same) ResponseFacade::failed(__("You cannot create a coin pair with the same base and trade coin"))->safeThrow();
        return $same;
    }

    /**
     * Check if coin pair already exists
     * @throws \Exception
     */
    public function hasCoinPair(Coin $baseCoin, Coin $tradeCoin): bool
    {
        $coinPair = FutureCoinPair::where('base_coin_id', $baseCoin->id)->where('trade_coin_id', $tradeCoin->id)->exists();
        if($coinPair) ResponseFacade::failed(__("Coin pair already exists"))->safeThrow();
        return false;
    }

    /**
     * Check if coin pair is default
     * @return bool
     */
    public function isDefaultCoinPair(FutureCoinPair $coinPair): bool
    {
        $defaultCoinPairId = AdminSetting::where('slug', 'future_trade_default_coin_pair')
            ->value('value');

        if (!$defaultCoinPairId) {
            return true;
        }

        return (int) $defaultCoinPairId === (int) $coinPair->id;
    }
}
