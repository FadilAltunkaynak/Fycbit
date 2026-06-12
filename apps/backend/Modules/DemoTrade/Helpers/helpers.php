<?php

use App\Model\Coin;
use App\Model\AdminSetting;
use Illuminate\Support\Carbon;
use Modules\DemoTrade\Entities\Buy;
use Modules\DemoTrade\Entities\Sell;
use Modules\DemoTrade\Entities\Wallet;
use Modules\DemoTrade\Entities\CoinPair;
use Modules\DemoTrade\Entities\DemoCoin;
use Modules\DemoTrade\Entities\UserWallet;
use Modules\DemoTrade\Entities\Transaction;
use Modules\DemoTrade\Entities\FaucetHistory;
use Modules\DemoTrade\Http\Services\CoinService;
use Modules\DemoTrade\Http\Services\DashboardService;
use Modules\DemoTrade\Http\Repositories\DashboardRepository;
use Modules\DemoTrade\Http\Repositories\UserWalletRepository;


function create_coin_demo_wallet($user_id)
{
    $items = getMissingCoinDemoWallet($user_id);
    if (!empty($items)) {
        foreach ($items as $item) {
            storeNewDemoWallet($item);
        }
    }
}

function storeNewDemoWallet($item)
{
    $checkWallet =  Wallet::where(['user_id' => $item['user_id'], 'coin_id' => $item['coin_id'], 'coin_type' => $item['coin_type']])->first();
    if (isset($checkWallet)) {
    } else {
        $checkWalletAgain =  Wallet::where(['user_id' => $item['user_id'], 'coin_id' => $item['coin_id']])->first();
        if (empty($checkWalletAgain)) {
            $coin = DemoCoin::where('coin_type' , $item['coin_type'])->first();
            $wallet = Wallet::firstOrCreate([
                'user_id' => $item['user_id'],
                'coin_id' => $item['coin_id']
            ],[
                'name' => $item['coin_type'].' wallet',
                'coin_type' => $item['coin_type'],
                'balance' => $coin->faucet_amount ?? 0
            ]);
        }
    }
}

function getMissingCoinDemoWallet($user_id)
{
    $coins = Coin::where(['status' => STATUS_ACTIVE, 'is_demo_trade' => STATUS_ACTIVE])->get();
    $data = [];
    if (isset($coins[0])) {
        foreach ($coins as $coin) {
            $exist = Wallet::where(['user_id' => $user_id, 'coin_id' => $coin->id])->first();
            if(isset($exist)) {
            } else {
                $data[] = [
                    'coin_id' => $coin->id,
                    'coin_type' => $coin->coin_type,
                    'user_id' => $user_id,
                    'name' => $coin->coin_type.' wallet',
                ];
            }
        }
    }
    return $data;
}

function checkDemoCoinPairDeleteCondition($coinPair)
{
    $response = ['success' => true, 'message' => __('Success')];
    $checkBuy = checkDemoBuyByCoin($coinPair->parent_coin_id,$coinPair->child_coin_id);
    if ($checkBuy > 0) {
        return ['success' => false, 'message' => __('This coin pair already have some buy order, so you should not delete this pair.')];
    }
    $checkSell = checkDemoSellByCoin($coinPair->parent_coin_id,$coinPair->child_coin_id);
    if ($checkSell > 0) {
        return ['success' => false, 'message' => __('This coin pair already have some sell order, so you should not delete this pair.')];
    }
    $checkOrder = checkDemoTransactionByCoin($coinPair->parent_coin_id,$coinPair->child_coin_id);
    if ($checkOrder > 0) {
        return ['success' => false, 'message' => __('This coin pair already have some transaction, so you should not delete this pair.')];
    }

    return $response;
}

function checkDemoBuyByCoin($baseCoinId,$tradeCoinId)
{
    $item = Buy::where(['base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId])->get();
    if (isset($item[0])) {
        return 1;
    }
    return 0;
}

function checkDemoSellByCoin($baseCoinId,$tradeCoinId)
{
    $item = Sell::where(['base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId])->get();
    if (isset($item[0])) {
        return 1;
    }
    return 0;
}

function checkDemoTransactionByCoin($baseCoinId,$tradeCoinId)
{
    $item = Transaction::where(['base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId])->get();
    if (isset($item[0])) {
        return 1;
    }
    return 0;
}

function calculated_demo_fee_limit($userId)
{
    $query = DB::connection('DemoTradeMysql')->select("select sum(btc) as total FROM transactions WHERE (buy_user_id = $userId or sell_user_id = $userId) AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
    $tradeVolume = $query[0]->total;
    $fees = [
        'maker_fees' => 0,
        'taker_fees' => 0,
        'thirtyDayVolume' => $tradeVolume,
    ];

    $limits = AdminSetting::where('slug', 'like', 'trade_limit_%')->get();

    $slugs = [];
    foreach ($limits as $limit) {
        if (bccomp($tradeVolume, $limit->value) !== -1) {
            $slugs[] = 'maker_' . explode('_', $limit->slug)[2];
            $slugs[] = 'taker_' . explode('_', $limit->slug)[2];
            $adminSetting = allsetting($slugs);
            $fees['maker_fees'] = $adminSetting['maker_' . explode('_', $limit->slug)[2]];
            $fees['taker_fees'] = $adminSetting['taker_' . explode('_', $limit->slug)[2]];
        }
    }

    return $fees;
}

function getDemoBtcRate($tradeCoinId)
{
    $btcCoin = Coin::where(['coin_type' => 'BTC'])->first();
    try {
        $dashboardService = new DashboardService();
        $response = $dashboardService->getLastPriceList($btcCoin->id, $tradeCoinId)->first();
        return $response->price;
    } catch (\Exception $e) {
        return 0;
    }
}

function getDemoService($data = [], $appName = null)
{
    try {
        $coinService = new CoinService();
        $response = $coinService->{$data['method']}($data['params']);
        return $response->toArray();
    } catch (\Exception $exception) {
        return false;
    }
}

function getDemoFirstPair()
{
    $pair = CoinPair::where(['status' => STATUS_ACTIVE])->first();
    if ($pair) {
        return $pair;
    }
    return false;
}

function checkDemoPair($baseCoinId,$tradeCoinId)
{
    $pair = CoinPair::where(['parent_coin_id' => $baseCoinId, 'child_coin_id' => $tradeCoinId, 'status' => STATUS_ACTIVE])->first();
    if ($pair) {
        return true;
    }
    return false;
}

function get_demo_base_coin_id($coin_type = null)
{
    $id = 2;
    if(isset($coin_type)) {
        $coin = Coin::where(['coin_type' => $coin_type])->first();
    } else {
        $pair = CoinPair::where(['status' => STATUS_ACTIVE])->first();
        if($pair) {
            $coin = Coin::where(['id' => $pair->parent_coin_id])->first();
        }
    }
    if (isset($coin)) {
        $id = $coin->id;
    }

    return $id;
}
function get_demo_trade_coin_id($coin_type = null)
{
    $id = 1;
    if(isset($coin_type)) {
        $coin = Coin::where(['coin_type' => $coin_type])->first();
    } else {
        $pair = CoinPair::where(['status' => STATUS_ACTIVE])->first();
        if($pair) {
            $coin = Coin::where(['id' => $pair->child_coin_id])->first();
        }
    }
    if (isset($coin)) {
        $id = $coin->id;
    }

    return $id;
}

/**
 * Broadcast Wallet data after balance Update
 * @param $walletId integer
 * @return void
 */
function demoBroadcastWalletData($walletId, $userId = null)
{

    $walletRepo = new UserWalletRepository(UserWallet::class);
    $wallet = $walletRepo->getById($walletId);
    $repo = new DashboardRepository();
    if ($userId != null) {
        $onOrder = $repo->getOnOrderBalance($wallet->coin_id, $userId);
    } else {
        $onOrder = $repo->getOnOrderBalance($wallet->coin_id);
    }
    $data = [
        'coin_id' => $wallet->coin_id,
        'balance' => $wallet->balance,
        'on_order' => $onOrder
    ];
    broadcastPrivate( 'updateWallet', $data, $wallet->user_id);

}

/**
 * broadcast all data like balance update, order delete, Trade History Update after transaction
 * @param $transaction object
 * @return void
 *
 */
function demoBroadcastTransactionData($transaction)
{
    $transaction = Transaction::select('base_coin_id', 'trade_coin_id', 'buy_user_id', 'sell_user_id', DB::connection('DemoTradeMysql')->raw("visualNumberFormat(amount) as amount"), DB::connection('DemoTradeMysql')->raw("visualNumberFormat(price) as price"), DB::connection('DemoTradeMysql')->raw("visualNumberFormat(last_price) as last_price"), DB::connection('DemoTradeMysql')->raw("visualNumberFormat(total) as total"), 'price_order_type', 'created_at', DB::connection('DemoTradeMysql')->raw("TIME(created_at) as time"), 'buy_fees', 'sell_fees', 'buy_user_id', 'sell_user_id')->where('id', $transaction->id)->first();

    $coinPairs = CoinPair::select('parent_coin_id', 'child_coin_id', DB::connection('DemoTradeMysql')->raw("visualNumberFormat(price) as price"), DB::connection('DemoTradeMysql')->raw("visualNumberFormat(volume) as volume"), DB::connection('DemoTradeMysql')->raw("`change`"), DB::connection('DemoTradeMysql')->raw("visualNumberFormat(high) as high"), DB::connection('DemoTradeMysql')->raw("visualNumberFormat(low) as low"))
        ->where(['parent_coin_id' => $transaction->base_coin_id, 'child_coin_id' => $transaction->trade_coin_id])->first()->toArray();

    $globalData = [
        'price' => $transaction->price,
        'price_order_type' => $transaction->price_order_type,
        'last_price' => $transaction->last_price,
        'amount' => $transaction->amount,
        'total' => $transaction->total,
        'unix_time' => strtotime($transaction->created_at),
        'time' => $transaction->time,
        'created_at' => $transaction->created_at,
        'base_coin_id' => $transaction->base_coin_id,
        'trade_coin_id' => $transaction->trade_coin_id,
        'buy_user_id' => $transaction->buy_user_id,
        'sell_user_id' => $transaction->sell_user_id,
        'buy_fees' => $transaction->buy_fees,
        'sell_fees' => $transaction->sell_fees
    ];

    broadcastPublic( 'transaction', $globalData);
    broadcastPublic( 'twentyFourHoursChange', $coinPairs);
    broadcastPrivate( 'transaction', $globalData, $transaction->buy_user_id);
    if ($transaction->buy_user_id != $transaction->sell_user_id) {
        broadcastPrivate( 'transaction', $globalData, $transaction->sell_user_id);
    }
}

function updateDemoCoins(){
    $coins = Coin::get();
        foreach ($coins as $coin) {
            if(! DB::connection("DemoTradeMysql")->table("demo_coins")->where('coin_type', $coin->coin_type)->first())
            {
                DB::connection("DemoTradeMysql")->table("demo_coins")->insert([
                    'coin_id' => $coin->id,
                    'coin_type' => $coin->coin_type,
                    'trade_status' => $coin->is_demo_trade ?? 0,
                    'faucet_amount' => 1000,
                ]);
            }else{
                DB::connection("DemoTradeMysql")->table("demo_coins")->where('coin_type', $coin->coin_type)->update([
                    'trade_status' => $coin->is_demo_trade ?? 0,
                ]);
            }
        }
}