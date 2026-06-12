<?php

namespace Modules\FutureTrade\Http\Controllers\Api;

use App\Facades\ResponseFacade;
use Illuminate\Routing\Controller;
use Modules\FutureTrade\Http\Requests\Api\FutureOrderCancelRequest;
use Modules\FutureTrade\Http\Requests\Api\FutureOrderHistoryFilterRequest;
use Modules\FutureTrade\Http\Requests\Api\FutureOrderRequest;
use Modules\FutureTrade\Http\Requests\Api\MyOrderListRequest;
use Modules\FutureTrade\Http\Requests\Api\OrderBookRequest;
use Modules\FutureTrade\Services\OrderService\OrderService;

class FutureOrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ){}
    public function order(FutureOrderRequest $request): mixed
    {
        $response = $this->orderService->orderProcess($request);
        return ResponseFacade::result($response)->send();
    }

    public function getMyOrderList(MyOrderListRequest $request): mixed
    {
        $response = $this->orderService->myOpenOrderList($request);
        return ResponseFacade::success($response)->send();
    }

    public function getOrderHistory(FutureOrderHistoryFilterRequest $request)
    {
        $response = $this->orderService->orderHistoryFilter($request);
        return ResponseFacade::success($response)->send();
    }

    public function orderCancel(FutureOrderCancelRequest $request)
    {
        $this->orderService->orderCancelAddQueue($request);
        return ResponseFacade::success(__('Order cancel processing'))->send();
    }

    public function getOrderbook(OrderBookRequest $request)
    {
        $response = $this->orderService->getOrderbook($request);
        return ResponseFacade::success($response)->send();
    }
}
