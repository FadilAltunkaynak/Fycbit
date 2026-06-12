<?php

namespace Modules\P2P\Jobs;

use Throwable;
use Illuminate\Bus\Queueable;
use Modules\P2P\Entities\POrder;
use Illuminate\Support\Facades\DB;
use Modules\P2P\Entities\P2PWallet;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ReleaseOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private $order
    )
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            DB::beginTransaction();
            $order = POrder::query()
                ->where("id", $this->order->id ?? 0)
                ->where('status', TRADE_STATUS_PAYMENT_DONE)
                ->where('is_queue', STATUS_ACTIVE)
                ->first();
            $tradeAmount = bcsub($order->amount,$order->buyer_fees,8);
            $buyerWallet = P2PWallet::find($order->buyer_wallet_id);
            $buyerWallet->increment('balance',$tradeAmount);
            $order->update(['status' => TRADE_STATUS_TRANSFER_DONE, 'is_success' => STATUS_ACTIVE, 'is_queue' => STATUS_INACTIVE]);
            // data send by websocket
            $channel_name1 = 'Order-Status-' . $order->buyer_id . $order->uid;
            $channel_name2 = 'Order-Status-' . $order->seller_id . $order->uid;
            $event_name = 'OrderStatus';
            SendEmailOnChangeState::dispatch(TRADE_STATUS_TRANSFER_DONE, $order)->onQueue("send-mail");
            sendDataThroughWebSocket($channel_name1, $event_name, [ 'order' => $order, 'message' => __("Seller released coins")]);
            sendDataThroughWebSocket($channel_name2, $event_name, [ 'order' => $order, 'message' => __("Seller released coins")]);
            DB::commit();
        } catch(\Exception $e) {
            DB::rollBack();
            storeException('p2pOrderPaymentProcess ex', $e->getMessage());
        }
    }

    public function failed(Throwable $exception): void
    {
        $this->order->update(['is_queue' => STATUS_INACTIVE]);
    }
}
