<?php

namespace Modules\P2P\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Modules\P2P\Entities\P2PWallet;
use Modules\P2P\Entities\POrder;

class ReleaseDisputeOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private $dispute_details)
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
        DB::beginTransaction();
        try {
            $dispute_details = $this->dispute_details;
            if ($dispute_details->status == STATUS_INACTIVE) {
                $order_details = POrder::find($dispute_details->order_id);
                if (isset($order_details)) {
                    $tradeAmount = bcsub($order_details->amount, $order_details->buyer_fees, 8);
                    $buyerWallet = getUserP2pWallet($order_details->coin_type, $order_details->buyer_id);
                    $buyerWallet->increment('balance', $tradeAmount);
                    $order_details->update(['status' => TRADE_STATUS_TRANSFER_DONE]);
                    $dispute_details->update(['status' => STATUS_ACCEPTED]);

                    storeException('RefundDisputeOrderJob', __('Order is released successfully'));
                } else {
                    storeException('RefundDisputeOrderJob', __('Order not found'));
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('ReleaseDisputeOrderJob ex', $e->getMessage());
        }
    }
}
