<?php

namespace Modules\FutureTrade\Http\Controllers\Api;

use App\Facades\ResponseFacade;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\FutureTrade\Entities\FutureLeverageSetting;
use Modules\FutureTrade\Entities\FutureUserSetting;
use Modules\FutureTrade\Http\Requests\Api\FutureLeverageSettingUpdateRequest;
use Modules\FutureTrade\Http\Requests\Api\FutureMarginModeUpdateRequest;
use Modules\FutureTrade\Repositories\CoinPairRepository\CoinPairRepository;
use Modules\FutureTrade\Services\LeverageSettingService\LeverageSettingService;
use Illuminate\Support\Str;

class FutureLeverageSettingController extends Controller
{
    public function getMaxLeverage(Request $request, LeverageSettingService $service)
    {
        $response = $service->getMaxLeverage($request);
        return ResponseFacade::result($response)->send();
    }

    public function getUserMarginLeverage(Request $request, LeverageSettingService $service)
    {
        if(! $request->filled('coin_pair_uid')){
            return ResponseFacade::failed(__('Coin pair uid is required'))->send();
        }

        $coinPair = CoinPairRepository::getCoinPairByUid($request->coin_pair_uid ?? '');

        if(! $coinPair){
            return ResponseFacade::failed(__('Coin pair not found'))->send();
        }

        $userSetting = $service::getUserFutureSetting(authId(), $coinPair->id);
        if (! $userSetting) {
            $userSetting = FutureUserSetting::create([
                'uid' => Str::uuid()->getHex(),
                'user_id' => authId(),
                'coin_pair_id' => $coinPair->id,
                'leverage' => 1,
                'margin_mode' => 1,
            ]);
        }

        $data = [
            'leverage' => $userSetting?->leverage ?? 1,
            'margin_mode' => $userSetting?->margin_mode?->value ?? 1,
        ];

        return ResponseFacade::success($data)->send();
    }

    public function marginUpdate(FutureMarginModeUpdateRequest $request, LeverageSettingService $service)
    {
        $response = $service->marginUpdate($request);
        return ResponseFacade::result($response)->send();
    }

    public function leverageUpdate(FutureLeverageSettingUpdateRequest $request, LeverageSettingService $service)
    {
        $response = $service->leverageSettingUpdate($request);
        return ResponseFacade::result($response)->send();
    }

    public function getLeverageSettingDataByLeverage(Request $request, LeverageSettingService $service)
    {
        if(! $request->filled('coin_pair_uid')){
            return ResponseFacade::failed(__('Coin pair uid is required'))->send();
        }

        if(! $request->filled('leverage')){
            return ResponseFacade::failed(__('Leverage is required'))->send();
        }

        $response = $service->getLeverageSettingData(
            coin_pair_uid: $request->coin_pair_uid,
            leverage: $request->leverage
        );
        return ResponseFacade::result($response)->send();
    }

    public function getAllLeverageSettingByCoinPair(Request $request): mixed
    {
        if(! $request->filled('coin_pair_uid')){
            return ResponseFacade::failed(__('Coin pair uid is required'))->send();
        }

        $settings = FutureLeverageSetting::byCoinPair($request->coin_pair_uid)->get();

        if($settings->isEmpty()){
            return ResponseFacade::failed(__('No leverage settings found for this coin pair'))->send();
        }

        return ResponseFacade::success($settings)->send();
    }
}
