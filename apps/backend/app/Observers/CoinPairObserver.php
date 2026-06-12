<?php

namespace App\Observers;

use App\Model\Coin;
use App\Model\CoinPair;
use App\Http\Services\LandingService;
use App\Http\Repositories\CoinPairRepository;

class CoinPairObserver
{
    /**
     * Handle the CoinPair "created" event.
     *
     * @param  \App\Model\CoinPair  $coinPair
     * @return void
     */
    public function created(CoinPair $coinPair)
    {
        $usdtCoin = Coin::where('coin_type', 'USDT')->first();
        if(isset($usdtCoin) && $coinPair->parent_coin_id == $usdtCoin->id)
        {
            $coins = Coin::where('id', $coinPair->child_coin_id)->first();
            if( isset($coins) )
            {
                $coins->coin_price = $coinPair->price;
                $coins->save();
            }
        }
        
    }

    /**
     * Handle the CoinPair "updated" event.
     *
     * @param  \App\Model\CoinPair  $coinPair
     * @return void
     */
    public function updated(CoinPair $coinPair)
    {
        $usdtCoin = Coin::where('coin_type', 'USDT')->first();
        if(isset($usdtCoin) && $coinPair->parent_coin_id == $usdtCoin->id)
        {
            $coins = Coin::where('id', $coinPair->child_coin_id)->first();
            if( isset($coins) )
            {
                $coins->coin_price = $coinPair->price;
                $coins->save();
            }
        }
        
        $landingService = new LandingService;
        $responseCoinStatisticList = $landingService->getMarketOverviewCoinStatisticListWebsocketData();
        $channel_name_CoinStatisticList = 'market-overview-coin-statistic-list-data';
        $event_name_CoinStatisticList = 'market-overview-coin-statistic-list';
        $socket_data_CoinStatisticList = $responseCoinStatisticList;

        sendDataThroughWebSocket($channel_name_CoinStatisticList,$event_name_CoinStatisticList,$socket_data_CoinStatisticList);

        $responseMarketTopCoinList = $landingService->getMarketOverviewTopCoinListWebsocketData($coinPair);
        $channel_name_MarketTopCoinList = 'market-overview-top-coin-list-data';
        $event_name_MarketTopCoinList = 'market-overview-top-coin-list';
        $socket_data_MarketTopCoinList = $responseMarketTopCoinList;

        sendDataThroughWebSocket($channel_name_MarketTopCoinList,$event_name_MarketTopCoinList,$socket_data_MarketTopCoinList);

        // send landing page market data through websocket connection
        $baseCoinType = settings('pair_assets_base_coin') ?? 'USDT';
        $baseCoin = null;

        if($baseCoinType == 'USDT') $baseCoin = $usdtCoin;
        else $baseCoin = Coin::where('coin_type', $baseCoinType)->first();
        
        if($baseCoin){
            if($coin_pair = Coin::find($coinPair->parent_coin_id)){
                if($baseCoin->coin_type == $coin_pair->coin_type){

                    $coinRepo = new CoinPairRepository(CoinPair::class);
                    $coinRepoData['asset_coin_pairs'] = $coinRepo->getLandingCoinPairs('asset');
                    $coinRepoData['hourly_coin_pairs'] = $coinRepo->getLandingCoinPairs('24hour');
                    $coinRepoData['latest_coin_pairs'] = $coinRepo->getLandingCoinPairs('latest');

                    $channel_name_MarketCoinPairData = 'market-coin-pair-data';
                    $event_name_MarketCoinPairData = 'market-coin-pairs';
            
                    sendDataThroughWebSocket($channel_name_MarketCoinPairData,$event_name_MarketCoinPairData,$coinRepoData);
                }
            }
        }
    }

    /**
     * Handle the CoinPair "deleted" event.
     *
     * @param  \App\Model\CoinPair  $coinPair
     * @return void
     */
    public function deleted(CoinPair $coinPair)
    {
        //
    }

    /**
     * Handle the CoinPair "restored" event.
     *
     * @param  \App\Model\CoinPair  $coinPair
     * @return void
     */
    public function restored(CoinPair $coinPair)
    {
        //
    }

    /**
     * Handle the CoinPair "force deleted" event.
     *
     * @param  \App\Model\CoinPair  $coinPair
     * @return void
     */
    public function forceDeleted(CoinPair $coinPair)
    {
        //
    }
}
