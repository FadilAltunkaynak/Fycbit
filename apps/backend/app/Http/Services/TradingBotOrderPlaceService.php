<?php
namespace App\Http\Services;

class TradingBotOrderPlaceService
{
    public function placeBotBuySellOrder($orderData, $pair, $user)
    {
        try {
            $order_1 = $orderData['order_1'];
            $order_2 = $orderData['order_2'];
            $rand = getRandomInt(1,0);
            $rand = $rand % 2;
            if ($rand == 0) {
                $order_1 = $orderData['order_1'];
                $order_2 = $orderData['order_2'];
            } else {
                $order_1 = $orderData['order_2'];
                $order_2 = $orderData['order_1'];
            }
            if ($order_1['price'] && $order_1['price'] > 0 && $order_1['amount']) {
                // process operation
                $this->processBuyOrSellOrder($order_1, $pair, $user);
            }

            if ($order_2['price'] && $order_2['price'] > 0 && $order_2['amount']) {
                // process operation
                $this->processBuyOrSellOrder($order_2, $pair, $user);
            }
        } catch(\Exception $e) {
            storeException('placeBotBuySellOrder', $e->getMessage());
        }
    }

    // place buy or sell order
    public function processBuyOrSellOrder($orderData, $pair, $user) {
        try {
            if($orderData['orderType'] == TRADE_TYPE_BUY) {
                $response = app(BuyOrderService::class)->createNewBotOrder($orderData,$pair,$user);
            }
            if($orderData['orderType'] == TRADE_TYPE_SELL) {
                $response = app(SellOrderService::class)->createNewBotOrder($orderData,$pair,$user);
            }
        } catch(\Exception $e) {
            storeBotException('bot processBuyOrSellOrder', $e->getMessage());
        }
    }

    // create buy order for known pair
    public function createMarketBuyOrder($pair,$marketData,$user){
        if ($marketData) {
            $amount = $marketData->buy_amount;
            $orderData['price']  = $marketData->buy_price;
            $orderData['amount'] = $amount;
            $response = app(BuyOrderService::class)->createNewBotOrder($orderData,$pair,$user);
        }
    }
    // create sell order for known pair
    public function createMarketSellOrder($pair,$marketData,$user){
        if ($marketData) {
            $amount = $marketData->sell_amount;
            $orderData['price'] = $marketData->sell_price;
            $orderData['amount'] = $amount;
            $response = app(SellOrderService::class)->createNewBotOrder($orderData,$pair,$user);
        }
    }
}
