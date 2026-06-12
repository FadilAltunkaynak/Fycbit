<?php

namespace Modules\P2P\Jobs;

use App\Model\Wallet;
use App\Model\GiftCard;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Modules\P2P\Entities\P2PWallet;
use Modules\P2P\Entities\PGiftCard;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Modules\P2P\Entities\PGiftCardOrder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GiftCardReleaseDisputeOrderJob implements ShouldQueue
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
                $order_details = PGiftCardOrder::find($dispute_details->gift_card_order_id);
                if (isset($order_details)) {

                    $ads = PGiftCard::find($order_details->p_gift_card_id);
                    $ads->update(['status' => GIFT_CARD_SUCCESS]);

                    $card = GiftCard::find($ads->gift_card_id);


                    $newCard = [
                        'uid' => uniqid() . date('') . time(),
                        'gift_card_banner_id' => $card->gift_card_banner_id,
                        'coin_type' => $card->coin_type,
                        'wallet_type' => $card->wallet_type,
                        'amount' => $card->amount,
                        'fees' => $card->fees,
                        'redeem_code' => date('') . time() . rand(11111, 99999),
                        'user_id' => $order_details->buyer_id,
                        'owner_id' => $order_details->buyer_id,
                        'status' => GIFT_CARD_STATUS_ACTIVE
                    ];
                    $gift_card = GiftCard::create($newCard);
                    $card->update(['status' => GIFT_CARD_STATUS_TRANSFARED]);


                    $order_details->update(['is_reported' => 0, 'status' => TRADE_STATUS_RELEASED_BY_ADMIN]);
                    $dispute_details->update(['status' => STATUS_ACCEPTED]);

                    storeException('GiftCardReleaseDisputeOrderJob', __('Order is released successfully'));
                } else {
                    storeException('GiftCardReleaseDisputeOrderJob', __('Order not found'));
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('GiftCardReleaseDisputeOrderJob ex', $e->getMessage());
        }
    }
}
