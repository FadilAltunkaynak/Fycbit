<?php

namespace Modules\DemoTrade\Http\Controllers\Api\User;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\DemoTrade\Entities\DemoCoin;
use Modules\DemoTrade\Http\Services\CoinService;
use Modules\DemoTrade\Http\Requests\GetWalletBalanceRequest;

class CoinController extends Controller
{
    public function getCoinList()
    {
        $coins = DemoCoin::where('trade_status', STATUS_ACTIVE)->get();
        $coins->map(function($q){
            $q->label = $q->coin_type;
            $q->value = number_format($q->faucet_amount, 8);
        });
        return response()->json(responseData(true, __("Coin get successfully"), $coins));
    }

    public function getWalletBalance(GetWalletBalanceRequest $request)
    {
        return response()->json(
            (new CoinService)->getWalletBallance($request)
        );
    }
}
