<?php

namespace Modules\FutureTrade\Repositories\OrderRepository;

use App\Http\Repositories\CommonRepository;
use Generator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\FutureTrade\DataObject\FutureOrderData;
use Modules\FutureTrade\Emum\OrderMethod;
use Modules\FutureTrade\Emum\OrderStatusEnum;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Emum\SortEnum;
use Modules\FutureTrade\Entities\FutureBuy;
use Modules\FutureTrade\Entities\FutureCoinPair;
use Modules\FutureTrade\Entities\FutureSell;
use Modules\FutureTrade\Http\Requests\Api\FutureOrderHistoryFilterRequest;
use Modules\FutureTrade\Http\Requests\Api\MyOrderListRequest;

class OrderRepository extends CommonRepository implements IOrderRepository
{
    /**
     * Constructor
     *
     * @param  FutureBuy|FutureSell  $model
     */
    public function __construct($model = null)
    {
        parent::__construct($model);
    }

    public function setModel(OrderType $orderType): void
    {
        $this->model = match ($orderType) {
            OrderType::BUY => FutureBuy::class,
            OrderType::SELL => FutureSell::class
        };
    }

    /**
     * Insert New Order Data
     */
    public function insertOrder(FutureOrderData $data): FutureBuy|FutureSell|null
    {
        try {
            return $this->create($data->toArray())?->refresh();
        } catch (\Throwable $th) {
            storeException('futureCreateOrder', $th->getLine());
            storeException('futureCreateOrder', $th->getMessage());

            return null;
        }
    }

    /**
     * Update Order
     */
    public function updateOrder(FutureBuy|FutureSell $order, FutureOrderData $data): FutureBuy|FutureSell|null
    {
        try {
            $update = $this->updateWhere([
                'id' => $order->id,
            ], $data->toArray());
            if ($update) {
                return $order;
            }

            return null;
        } catch (\Throwable $th) {
            storeException('futureUpdateOrder', $th->getLine());
            storeException('futureUpdateOrder', $th->getMessage());

            return null;
        }
    }

    public function openOrderList(FutureCoinPair $coinPair, $limit = 100, SortEnum $sort = SortEnum::ASC)
    {
        return $this->model::byPair($coinPair->id)
            ->pendingOrder()
            ->orderBy('price', $sort->label())
            ->limit($limit)->get();
    }

    public function getPendingAmount(FutureBuy|FutureSell $order): float
    {
        $this->setModel($order->order_type);
        $order = $this->model::find($order->id);

        return $order?->pending_amount ?? 0;
    }

    public function getOrderBuilder(
        int $coinPair,
        OrderType $orderType,
    ): Builder {
        $this->setModel($orderType);
        return $this->model::byPair($coinPair);
    }

    public function getPendingOrdersBuilder(
        int $coinPair,
        OrderType $orderType,
    ): Builder {

        return $this->getOrderBuilder($coinPair, $orderType)->pendingOrder()
            ->select(
                'id',
                'future_coin_pair_id',
                'user_id',
                'price',
                'pending_amount',
            )
            ->where('status', OrderStatusEnum::PENDING->value)
            ->orderBy('id', SortEnum::ASC->label());
    }

    public function getOrders(
        int $coinPair,
        OrderType $orderType,
        OrderMethod $orderMethod,
        float|string $price = 0,
        $limit = 100,
        int $page = 0,
        bool $paginate = true
    ): LengthAwarePaginator|Builder {
        $this->setModel($orderType);

        $sort = match ($orderType) {
            OrderType::BUY => SortEnum::DESC,
            OrderType::SELL => SortEnum::ASC
        };

        $orderMatchingBy = match ($orderType) {
            OrderType::BUY => '>=',
            OrderType::SELL => '<='
        };

        $ordersQuery = $this->model::byPair($coinPair)->pendingOrder()
            ->select('id', 'future_coin_pair_id', 'user_id', 'price', 'pending_amount', 'order_type', 'is_bot')
            ->where('order_method', OrderMethod::LIMIT->value)
            ->where('stop_price', 0)
            ->when(
                $price > 0 &&
                OrderMethod::MARKET !== $orderMethod,
                fn($q) => $q->where('price', $orderMatchingBy, $price)
            )
            ->orderBy('price', $sort->label())
            ->orderBy('id', SortEnum::ASC->label());

        if ($paginate) {
            return $ordersQuery->paginate($limit, ['*'], 'page', $page);
        }

        return $ordersQuery;
    }

    public function getOrdersRecursive(
        int $coinPair,
        OrderType $orderType,
        OrderMethod $orderMethod,
        float|string $price = 0,
        $limit = 100,
    ): Generator {
        $lastPage = 1;
        $currentPage = 0;

        while ($currentPage < $lastPage) {
            $orders = $this->getOrders(
                coinPair: $coinPair,
                orderType: $orderType,
                orderMethod: $orderMethod,
                price: $price,
                limit: $limit,
                page: ++$currentPage
            );

            $lastPage = $orders->lastPage();
            $currentPage = $orders->currentPage();
            $orders = $orders->items();

            yield $orders;
        }
    }

    public function getOrderById(int $orderId, OrderType $orderType): Builder
    {
        $this->setModel($orderType);
        return $this->model::where('id', $orderId);
    }

    public function getOrderByUid(string $orderUid, OrderType $orderType): Builder
    {
        $this->setModel($orderType);
        return $this->model::where('uid', $orderUid);
    }

    public function orderHistoryFilter(
        FutureOrderHistoryFilterRequest|MyOrderListRequest $request,
        ?OrderStatusEnum $orderStatus = null
    ): LengthAwarePaginator {
        $userId = authId();
        $side = $request->filled('side')
            ? OrderType::tryFrom((int) $request->side)
            : null;

        $applyFilters = function ($query, string $table) use ($request, $orderStatus) {
            if ($request->filled('symbol')) {
                $query->where('future_coin_pairs.code', $request->symbol);
            }

            if ($request->filled('order_method')) {
                $query->where("$table.order_method", (int) $request->order_method);
            }

            if ($orderStatus) {
                $query->where("$table.status", $orderStatus->value);
            }

            if ($request->filled('time')) {
                $to = now();
                $from = $to->copy()->subDays((int) $request->time);
                $query->whereBetween("$table.created_at", [$from, $to]);
            }

            if (
                !$request->filled('time') &&
                $request->filled('time_from') &&
                $request->filled('time_to')
            ) {
                $query->whereBetween("$table.created_at", [
                    Carbon::parse($request->time_from),
                    Carbon::parse($request->time_to),
                ]);
            }
        };

        $direction = SortEnum::tryFrom($request->sort ?? 0)?? SortEnum::DESC;
        $direction = $direction->label();

        $limit = (int) ($request->limit ?? 20);
        $page = max((int) ($request->page ?? 1), 1);

        $buildQuery = function (string $modelClass, string $table, int $sideValue) use ($userId, $applyFilters) {
            $query = $modelClass::query()
                ->select([
                    "$table.uid",
                    "$table.order_method",
                    "$table.created_at",
                    "$table.price",
                    "$table.amount",
                    "$table.processed_amount",
                    "$table.is_reduce",
                    "$table.tp_price",
                    "$table.sl_price",
                    "$table.status",
                    'future_coin_pairs.code',
                    'future_coin_pairs.base_coin_code',
                    'future_coin_pairs.trade_coin_code',
                    'future_coin_pairs.trade_decimal',
                    'future_coin_pairs.base_decimal',
                    DB::raw("$sideValue as side"),
                ])
                ->join('future_coin_pairs', 'future_coin_pairs.id', '=', "$table.future_coin_pair_id")
                ->where("$table.user_id", $userId);

            $applyFilters($query, $table);

            return $query;
        };

        $buyTable = (new FutureBuy())->getTable();
        $sellTable = (new FutureSell())->getTable();
        $buyQuery = $buildQuery(FutureBuy::class, $buyTable, 1);
        $sellQuery = $buildQuery(FutureSell::class, $sellTable, 2);

        if ($side === OrderType::BUY) {
            return $buyQuery
                ->orderBy("$buyTable.created_at", $direction)
                ->paginate($limit, ['*'], 'page', $page);
        }

        if ($side === OrderType::SELL) {
            return $sellQuery
                ->orderBy("$sellTable.created_at", $direction)
                ->paginate($limit, ['*'], 'page', $page);
        }

        $union = $buyQuery->unionAll($sellQuery);

        return DB::query()
            ->fromSub($union, 'orders')
            ->orderBy('created_at', $direction)
            ->paginate($limit, ['*'], 'page', $page);
    }
}
