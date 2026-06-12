<?php

namespace Modules\P2P\Jobs;

use Illuminate\Bus\Queueable;
use Modules\P2P\Entities\PSell;
use Illuminate\Support\Facades\DB;
use Modules\P2P\Entities\P2PWallet;
use Illuminate\Queue\SerializesModels;
use Modules\P2P\Entities\POrderCancel;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CancelOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private $order,
        private $id,
        private $reason,
    ) {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            $order = $this->order;
            if ($order) {
                $cancelData = [
                    'order_id' => $order->id,
                    'type' => $order->buy_id ? TRADE_BUY_TYPE : TRADE_SELL_TYPE,
                    'user_id' => $this->id,
                    'partner_id' => ($order->buyer_id == $this->id) ? $order->seller_id : $order->buyer_id,
                    'reason_heading' => $this->reason,
                ];
                if (isset($order->sell_id)) {
                    $tradeAmount = bcadd($order->amount, $order->buyer_fees, 8);
                    $sell_details = PSell::find($order->sell_id);
                    $sell_details->increment('available', $tradeAmount);
                    $sell_details->decrement('sold', $tradeAmount);
                } else {
                    $tradeAmount = bcadd($order->amount, $order->buyer_fees, 8);
                    $buyerWallet = P2PWallet::find($order->seller_wallet_id);
                    $buyerWallet->increment('balance', $tradeAmount);
                }
                $order->status = TRADE_STATUS_CANCELED;
                $order->who_cancelled = $this->id;
                $order->is_queue = STATUS_INACTIVE;
                $order->save();
                POrderCancel::create($cancelData);
                storeException("CancelOrderJob -  Order $order->order_id", " Canceled successfully ");
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('CancelOrderJob ex', $e->getMessage());
        }
    }
}
