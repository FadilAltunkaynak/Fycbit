<?php

namespace Modules\DemoTrade\Http\Controllers\Api\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\DemoTrade\Http\Services\SellOrderService;
use Modules\DemoTrade\Http\Services\StopLimitService;
use Modules\DemoTrade\Http\Validators\SellOrderValidator;
use Modules\DemoTrade\Http\Validators\StopLimitValidators;

class SellOrderController extends Controller
{
    /**
     * @param SellOrderValidator $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeSellLimitOrderApp(SellOrderValidator $request)
    {
        $request->merge([
            'is_market'=>0
        ]);
        if ($request->trade_coin_id == $request->base_coin_id) {
            response()->json( [
                'status' => false,
                'message' => __('Base coin and trade coin should be different'),
            ]);
        }
        $response = app(SellOrderService::class)->create($request);
        if ($response['status'] == false) {
            return response()->json($response);
        }

        return response()->json($response);
    }

    /**
     * @param SellOrderValidator $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeSellMarketOrderApp(SellOrderValidator $request)
    {
        $request->merge([
            'is_market'=>1
        ]);
        if ($request->trade_coin_id == $request->base_coin_id) {
            response()->json( [
                'status' => false,
                'message' => __('Base coin and trade coin should be different'),
            ]);
        }
        $response = app(SellOrderService::class)->create($request);
        if ($response['status'] == false) {
            return response()->json($response);
        }

        return response()->json($response);
    }

    /**
     * @param StopLimitValidators $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeStopLimitSellOrderApp(StopLimitValidators $request)
    {
        $request->merge([
            'order'=>'sell'
        ]);
        if ($request->trade_coin_id == $request->base_coin_id) {
            response()->json( [
                'status' => false,
                'message' => __('Base coin and trade coin should be different'),
            ]);
        }
        $service = new StopLimitService();
        $response =  $service->create($request);
        return response()->json($response);
    }
}
