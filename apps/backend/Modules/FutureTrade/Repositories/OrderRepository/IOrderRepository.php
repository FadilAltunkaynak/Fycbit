<?php

namespace Modules\FutureTrade\Repositories\OrderRepository;

use Generator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\FutureTrade\DataObject\FutureOrderData;
use Modules\FutureTrade\Emum\OrderMethod;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Entities\FutureBuy;
use Modules\FutureTrade\Entities\FutureSell;

interface IOrderRepository
{
    public function setModel(OrderType $orderType): void;

    /**
     * Insert New Order Data
     */
    public function insertOrder(FutureOrderData $data): FutureBuy|FutureSell|null;

    /**
     * Update Order
     */
    public function updateOrder(FutureBuy|FutureSell $order, FutureOrderData $data): FutureBuy|FutureSell|null;

    /**
     * Get Paginate Orders By OrderType
     */
    public function getOrders(
        int $coinPair,
        OrderType $orderType,
        OrderMethod $orderMethod,
        float|string $price = 0,
        int|string $limit = 100,
        int $page = 0,
        bool $paginate = true
    ): LengthAwarePaginator|Builder;

    /**
     * Get Orders Recursively By OrderType
     */
    public function getOrdersRecursive(
        int $coinPair,
        OrderType $orderType,
        OrderMethod $orderMethod,
        float|string $price = 0,
        int|string $limit = 100,
    ): Generator;

    public function getOrderBuilder(
        int $coinPair,
        OrderType $orderType,
    ): Builder;

    public function getPendingOrdersBuilder(
        int $coinPair,
        OrderType $orderType,
    ): Builder;

    public function getOrderById(int $orderId, OrderType $orderType): Builder;
}
