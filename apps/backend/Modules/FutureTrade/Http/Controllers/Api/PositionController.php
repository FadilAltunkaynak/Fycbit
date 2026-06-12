<?php

namespace Modules\FutureTrade\Http\Controllers\Api;

use App\Facades\ResponseFacade;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FutureTrade\Http\Requests\Api\FuturePositionHistoryFilterRequest;
use Modules\FutureTrade\Http\Requests\Api\PositionIsolatedMarginUpdateRequest;
use Modules\FutureTrade\Http\Requests\Api\PositionTpSlCancelRequest;
use Modules\FutureTrade\Http\Requests\Api\PositionTpSlUpdateRequest;
use Modules\FutureTrade\Services\FuturePositionServices\FuturePositionService;

class PositionController extends Controller
{
    public function updateIsolatedMargin(
        PositionIsolatedMarginUpdateRequest $request,
        FuturePositionService $service
    ): mixed {
        $response = $service->addMarginOnIsolatedPosition(
            coin_pair_uid: $request->coin_pair_uid,
            amount: $request->amount,
            action: (int) $request->action
        );

        return ResponseFacade::result($response)->send();
    }

    public function updateTpSl(PositionTpSlUpdateRequest $request, FuturePositionService $service)
    {
        $response = $service->updateTpSl($request);
        return ResponseFacade::result($response)->send();
    }

    public function cancelTpSl(PositionTpSlCancelRequest $request, FuturePositionService $service)
    {
        $response = $service->cancelTpSl($request);
        return ResponseFacade::result($response)->send();
    }

    public function getMyPositions(FuturePositionService $service)
    {
        $response = $service->getMyOpenPositions();
        return ResponseFacade::result($response)->send();
    }

    public function getMyPositionHistory(FuturePositionHistoryFilterRequest $request, FuturePositionService $service)
    {
        $response = $service->getMyOpenPositionHistory($request);
        return ResponseFacade::success($response)->send();
    }

    public function getUserMarginSummary(Request $request, FuturePositionService $service): mixed
    {
        $uid = $request->input('coin_pair_uid');
        if (!$uid) {
            ResponseFacade::failed(__('Coin pair uid is required'))->throw();
        }
        $response = $service->getUserMarginSummary($uid);

        return ResponseFacade::success($response)->send();
    }

    public function getUserAssetsSummary(Request $request, FuturePositionService $service): mixed
    {
        $uid = $request->input('coin_pair_uid');
        if (!$uid) {
            ResponseFacade::failed(__('Coin pair uid is required'))->throw();
        }
        $response = $service->getUserAssetsSummary($uid);

        return ResponseFacade::success($response)->send();
    }
}
