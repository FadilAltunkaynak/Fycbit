<?php

namespace App\Services\TradingBotServices;

use App\Contracts\Repositories\Factories\OrderRepositoryFactoryInterface;
use App\Dtos\OrderProcessingDTO;
use App\Http\Services\BuySellTransactionService;
use App\Http\Services\TradeServices\TradeService;
use App\Jobs\TradeDataBroadcastJob;
use App\Services\TradingBotServices\BotOrderService;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class BotTradeService
{
    const OPPOSITE_ORDER_TYPE = [
        'buy' => 'sell',
        'sell' => 'buy',
    ];

    public function __construct(
        private BuySellTransactionService $buySellTransactionService,
        private OrderRepositoryFactoryInterface $orderRepositoryFactory,
        private TradeService $tradeService
    ) {}

    public function process(OrderProcessingDTO $data): bool
    {
        if (! array_key_exists($data->type, self::OPPOSITE_ORDER_TYPE)) {
            storeBotException('Trade Processor', 'Invalid type '.$data->type);
            throw new InvalidArgumentException('Invalid type '.$data->type);
        }

        if(! $data->isBotOrder){
            return $this->tradeService->process($data);
        }

        $order = $data->order;
        if (! $order || $order->status == 1 || $order->amount == $order->processed) {
            return false;
        }

        $botService = app(BotOrderService::class);
        $botService->order($order, $data->matchedOrderId, $data->type);

        TradeDataBroadcastJob::dispatch($order, null);
        return true;
    }
}
