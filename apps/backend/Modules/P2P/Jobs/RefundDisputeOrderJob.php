<?php

namespace Modules\P2P\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\P2P\Entities\P2PWallet;
use Modules\P2P\Entities\POrder;
use Modules\P2P\Entities\PSell;
use Illuminate\Support\Facades\DB;

class RefundDisputeOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private $dispute_details)
    {

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
            $dispute_details = $this->dispute_details;

            if ($dispute_details->status == STATUS_INACTIVE) {
                $order_details = POrder::find($dispute_details->order_id);
                if (isset($order_details)) {
                    if (isset($order_details->sell_id)) {
                        $tradeAmount = bcadd($order_details->amount, $order_details->buyer_fees, 8);
                        $sell_details = PSell::find($order_details->sell_id);
                        $sell_details->increment('available', $tradeAmount);
                        $sell_details->decrement('sold', $tradeAmount);
                        $order_details->update(['status' => TRADE_STATUS_REFUNDED_BY_ADMIN]);

                        $dispute_details->update(['status' => STATUS_ACCEPTED]);

                        storeException('RefundDisputeOrderJob', __('Order is refunded successfully'));
                    } else {
                        $tradeAmount = bcadd($order_details->amount, $order_details->buyer_fees, 8);
                        $buyerWallet = P2PWallet::find($order_details->seller_wallet_id);
                        $buyerWallet->increment('balance', $tradeAmount);
                        $order_details->update(['status' => TRADE_STATUS_REFUNDED_BY_ADMIN]);
                        $dispute_details->update(['status' => STATUS_ACCEPTED]);

                        storeException('RefundDisputeOrderJob', __('Order is refunded successfully'));

                    }
                } else {
                    storeException('RefundDisputeOrderJob', __('Order not found'));
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('RefundDisputeOrderJob ex', $e->getMessage());
        }
    }
}
