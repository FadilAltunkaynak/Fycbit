<?php

namespace App\Listeners;

use App\Contracts\Repositories\Factories\OrderRepositoryFactoryInterface;
use App\Dtos\OrderProcessingDTO;
use App\Events\BotOrderHasPlaced;
use App\Http\Services\BuySellTransactionService;
use App\Http\Services\DashboardService;
use App\Services\TradingBotServices\BotTradeService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BotStartProcessingOrder
{
    const OPPOSITE_ORDER_TYPE = [
        'buy' => 'sell',
        'sell' => 'buy',
    ];

    const PRICE_COMPARATOR = [
        'buy' => '<=',
        'sell' => '>=',
    ];

    const ORDER_BY_DIRECTION = [
        'buy' => 'asc',
        'sell' => 'desc',
    ];

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        private BuySellTransactionService $tradeService,
        private BotTradeService $tradeProcessor,
        private OrderRepositoryFactoryInterface $orderRepositoryFactory
    ) {}

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(BotOrderHasPlaced $event)
    {
        try {
            $type = strtolower(Str::singular($event->order->getTable()));
            $orderRepository = $this->orderRepositoryFactory->getOrderRepositoryByType(self::OPPOSITE_ORDER_TYPE[$type]);

            foreach ($orderRepository->getMatchedOrders(
                $event->order->base_coin_id,
                $event->order->trade_coin_id,
                $event->order->is_market,
                $event->order->price,
                self::ORDER_BY_DIRECTION[$type],
                self::PRICE_COMPARATOR[$type],
                $event->order->amount
            ) as $matchedOrders) {
                $shouldProcessNextChunk = $this->processChunkedOrders(
                    $matchedOrders,
                    $event,
                    $type
                );

                if (! $shouldProcessNextChunk) {
                    break;
                }
            }
            $event->order->refresh();
            if (
                $event->order->status == 0
                // && ! app('spot_orderbook_cache')->hasProperty("order:{$type}:{$event->order->id}", 'id')
            ) {
                app('spot_orderbook_cache')->addOpenOrder($event->order, $type);
            }

            $this->broadcastOrderData($event->order, $type);
        } catch (\Exception $e) {
            storeException('Event Error', $e);
        }
    }

    private function processChunkedOrders($matchedOrders, $event, $type): bool
    {
        foreach ($matchedOrders as $matchedOrder) {

            $data = new OrderProcessingDTO(
                $event->order->id,
                $matchedOrder->id,
                $type,
                $matchedOrder->is_bot
            );
            $data->order = $event->order;

            $shouldProcessNextMatchedOrders = $this->tradeProcessor->process($data);

            if (! $shouldProcessNextMatchedOrders) {
                return false;
            }
        }

        return true;
    }

    private function broadcastOrderData($order, $type)
    {
        $requestData = [
            'dashboard_type' => 'dashboard',
            'order_type' => $type,
            'base_coin_id' => $order->base_coin_id,
            'trade_coin_id' => $order->trade_coin_id
        ];
        $request = new Request($requestData);

        $d_service = new DashboardService();

        $socket_data = $d_service->getAllOrderSocketData($request);
        $channel_name = 'dashboard-' . $request->base_coin_id . '-' . $request->trade_coin_id;
        $event_name = 'order_place';
        sendDataThroughWebSocket($channel_name, $event_name, $socket_data);

        broadcastOrderData($order, $type, 'orderPlace');
    }
}
