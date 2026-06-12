<?php

namespace Modules\FutureTrade\Http\Controllers;

use App\Facades\ResponseFacade;
use App\Model\Coin;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FutureTrade\Emum\OrderMethod;
use Modules\FutureTrade\Emum\OrderStatusEnum;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Entities\FutureBuy;
use Modules\FutureTrade\Entities\FutureCoinPair;
use Modules\FutureTrade\Entities\FutureSell;
use Modules\FutureTrade\Http\Requests\Api\FutureOrderCancelRequest;
use Modules\FutureTrade\Http\Requests\FutureCoinPairRequest;
use Modules\FutureTrade\Repositories\OrderRepository\OrderRepository;
use Modules\FutureTrade\Services\CoinPairService\CoinPairService;
use Modules\FutureTrade\Services\OrderService\OrderService;

class FutureOrderController extends Controller
{
    public function buyOrderList(): mixed
    {
        if (IS_API_CALL) {
            $query = FutureBuy::query()
                ->join('future_coin_pairs', 'future_coin_pairs.id', '=', 'future_buys.future_coin_pair_id')
                ->join('users', 'users.id', '=', 'future_buys.user_id')
                ->select(
                    'future_buys.*',
                    'users.email',
                    'future_coin_pairs.code',
                    'future_coin_pairs.trade_decimal',
                    'future_coin_pairs.base_coin_code',
                    'future_coin_pairs.trade_coin_code'
                );
            return datatables($query)
                ->addColumn('code', fn($order) => $order?->code ?? 'N/A')
                ->filterColumn('code', function ($query, $keyword) {
                    $query->where('future_coin_pairs.code', 'LIKE', "%$keyword%");
                })
                ->filterColumn('uid', function ($query, $keyword) {
                    $query->where('future_buys.uid', 'LIKE', "%$keyword%");
                })
                ->addColumn('user', fn($order) => $order?->email ?? 'N/A')
                ->filterColumn('user', function ($query, $keyword) {
                    $query->where('users.email', 'LIKE', "%$keyword%");
                })
                ->addColumn('type', fn($order) => $order?->order_method?->label() ?? 'N/A')
                ->filterColumn('type', function ($query, $keyword) {
                    $status = match (true) {
                        str_contains(strtolower($keyword), 'lim') => OrderMethod::LIMIT->value,
                        str_contains(strtolower($keyword), 'mark') => OrderMethod::MARKET->value,
                        str_contains(strtolower($keyword), 'stop') => OrderMethod::STOP_LIMIT->value,
                        default => 11
                    };
                    if ($status == 11)
                        return;
                    $query->where('future_buys.order_method', $status);
                })
                ->addColumn('price', fn($order) => $order->price . ' ' . $order->trade_coin_code ?? '')
                ->filterColumn('price', function ($query, $keyword) {
                    $query->where('future_buys.price', 'LIKE', "%$keyword%");
                })
                ->addColumn('amount', fn($order) => trim_num($order->amount, $order->trade_decimal ?: 8) . ' ' . $order->base_coin_code ?? '')
                ->filterColumn('amount', function ($query, $keyword) {
                    $query->where('future_buys.amount', 'LIKE', "%$keyword%");
                })
                ->addColumn(
                    'total',
                    fn($order) =>
                    trim_num(
                        bcmulx(
                            $order->price,
                            $order->amount,
                            $order->trade_decimal ?? 8
                        ),
                        $order->trade_decimal ?: 8
                    ) . ' ' . $order->trade_coin_code ?? ''
                )
                ->editColumn('created_at', '{{ date("d-m-Y H:i a", strtotime($created_at)) }}')
                ->editColumn('status', fn($item) => $item->status->label())
                ->filterColumn('status', function ($query, $keyword) {
                    $status = match (true) {
                        str_contains(strtolower($keyword), 'comp') => OrderStatusEnum::COMPLETE->value,
                        str_contains(strtolower($keyword), 'canc') => OrderStatusEnum::CANCEL->value,
                        str_contains(strtolower($keyword), 'ope') => OrderStatusEnum::PENDING->value,
                        default => 11
                    };
                    if ($status == 11)
                        return;
                    $query->where('future_buys.status', $status);
                })
                ->editColumn('action', 'futureTrade::orders.components.action')
                ->rawColumns(['base_coin_code', 'trade_coin_code', 'status', 'action'])
                ->make(true);
        }
        $data['title'] = __('Future Coin Pairs');
        return view('futureTrade::orders.buy-list', $data);
    }

    public function sellOrderList(): mixed
    {
        if (IS_API_CALL) {
            $query = FutureSell::query()
                ->join('future_coin_pairs', 'future_coin_pairs.id', '=', 'future_sells.future_coin_pair_id')
                ->join('users', 'users.id', '=', 'future_sells.user_id')
                ->select(
                    'future_sells.*',
                    'users.email',
                    'future_coin_pairs.code',
                    'future_coin_pairs.trade_decimal',
                    'future_coin_pairs.base_coin_code',
                    'future_coin_pairs.trade_coin_code'
                );
            return datatables($query)
                ->addColumn('code', fn($order) => $order?->code ?? 'N/A')
                ->filterColumn('code', function ($query, $keyword) {
                    $query->where('future_coin_pairs.code', 'LIKE', "%$keyword%");
                })
                ->filterColumn('uid', function ($query, $keyword) {
                    $query->where('future_sells.uid', 'LIKE', "%$keyword%");
                })
                ->addColumn('user', fn($order) => $order?->email ?? 'N/A')
                ->filterColumn('user', function ($query, $keyword) {
                    $query->where('users.email', 'LIKE', "%$keyword%");
                })
                ->addColumn('type', fn($order) => $order?->order_method?->label() ?? 'N/A')
                ->filterColumn('type', function ($query, $keyword) {
                    $status = match (true) {
                        str_contains(strtolower($keyword), 'lim') => OrderMethod::LIMIT->value,
                        str_contains(strtolower($keyword), 'mark') => OrderMethod::MARKET->value,
                        str_contains(strtolower($keyword), 'stop') => OrderMethod::STOP_LIMIT->value,
                        default => 11
                    };
                    if ($status == 11)
                        return;
                    $query->where('future_sells.order_method', $status);
                })
                ->addColumn('price', fn($order) => $order->price . ' ' . $order->trade_coin_code ?? '')
                ->filterColumn('price', function ($query, $keyword) {
                    $query->where('future_sells.price', 'LIKE', "%$keyword%");
                })
                ->addColumn('amount', fn($order) => trim_num($order->amount, $order->trade_decimal ?: 8) . ' ' . $order->base_coin_code ?? '')
                ->filterColumn('amount', function ($query, $keyword) {
                    $query->where('future_sells.amount', 'LIKE', "%$keyword%");
                })
                ->addColumn(
                    'total',
                    fn($order) =>
                    trim_num(
                        bcmulx(
                            $order->price,
                            $order->amount,
                            $order->trade_decimal ?? 8
                        ),
                        $order->trade_decimal ?: 8
                    ) . ' ' . $order->trade_coin_code ?? ''
                )
                ->editColumn('created_at', '{{ date("d-m-Y H:i a", strtotime($created_at)) }}')
                ->editColumn('status', fn($item) => $item->status->label())
                ->filterColumn('status', function ($query, $keyword) {
                    $status = match (true) {
                        str_contains(strtolower($keyword), 'comp') => OrderStatusEnum::COMPLETE->value,
                        str_contains(strtolower($keyword), 'canc') => OrderStatusEnum::CANCEL->value,
                        str_contains(strtolower($keyword), 'ope') => OrderStatusEnum::PENDING->value,
                        default => 11
                    };
                    if ($status == 11)
                        return;
                    $query->where('future_sells.status', $status);
                })
                ->editColumn('action', 'futureTrade::orders.components.action')
                ->rawColumns(['base_coin_code', 'trade_coin_code', 'status', 'action'])
                ->make(true);
        }
        $data['title'] = __('Future Coin Pairs');
        return view('futureTrade::orders.sell-list', $data);
    }

    public function getOrder(Request $req, OrderService $service)
    {
        if (!$req->filled('type')) {
            return ResponseFacade::failed(__('Order type is required'))->send();
        }

        $orderType = OrderType::tryFrom($req->type);

        if (!$orderType) {
            return ResponseFacade::failed(__('Order type is invalid'))->send();
        }

        if (!$req->filled('uid')) {
            return ResponseFacade::failed(__('Order uid is required'))->send();
        }

        $order = $service->repository->getOrderByUid($req->uid, $orderType)
            ->with(['coinPair:id,uid,code,trade_decimal,base_coin_code,trade_coin_code', 'user:id,email'])->first();

        $orderHtmlData = view('futureTrade::orders.components.order-details', ['order' => $order])->render();

        return ResponseFacade::success(['html' => $orderHtmlData])->send();
    }

    public function cancelOrder(Request $request, OrderService $service): mixed
    {
        if (!$request->filled('type')) {
            return ResponseFacade::failed(__('Order type is required'))->send();
        }

        $orderType = OrderType::tryFrom($request->type);

        if (!$orderType) {
            return ResponseFacade::failed(__('Order type is invalid'))->send();
        }

        if (!$request->filled('uid')) {
            return ResponseFacade::failed(__('Order uid is required'))->send();
        }

        $order = $service->repository->getOrderByUid($request->uid, $orderType)->first();
        if (!$order) {
            return ResponseFacade::failed(__('Order not found'))->send();
        }

        $req = new FutureOrderCancelRequest();
        $req->merge([
            "order_uid" => $request->uid,
            'order_type' => $orderType->value
        ]);

        $service->orderCancelAddQueue($req, $order->user_id);
        return ResponseFacade::success(__('Order cancel processing'))->send();
    }
}
