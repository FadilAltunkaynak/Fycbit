<?php
namespace Modules\P2P\Http\Service;
use App\User;
use Carbon\Carbon;
use App\Model\Coin;
use App\Model\Wallet;
use App\Model\GiftCard;
use App\Model\CountryList;
use App\Model\GiftCardBanner;
use Illuminate\Support\Facades\DB;
use Modules\P2P\Entities\P2PWallet;
use Modules\P2P\Entities\PGiftCard;
use App\Model\DynamicBank\BankRecord;
use Modules\P2P\Entities\PPaymentTime;
use Modules\P2P\Entities\PGiftCardChat;
use Modules\P2P\Entities\PGiftCardOrder;
use Modules\P2P\Entities\PGiftCardFeedback;
use Modules\P2P\Entities\PGiftCardOrderDisputes;
use Modules\P2P\Jobs\GiftCardReleaseDisputeOrderJob;

class GiftCardService
{

    public function storeGiftCardAdds($request)
    {
        $user = authUser_p2p();
        try {

            $giftCardDetails = GiftCard::where('id', $request->gift_card_id)
                ->where('user_id', $user->id)
                ->where('status', GIFT_CARD_STATUS_ACTIVE)
                ->where('lock', STATUS_INACTIVE)
                ->first();

            if (isset($giftCardDetails)) {
                $checkAds = PGiftCard::where('user_id', $user->id)
                    ->where('gift_card_id', $request->gift_card_id)
                    ->first();

                if (!isset($checkAds)) {
                    $newAds = new PGiftCard;
                    $newAds->uid = pMakeUniqueId();
                    $newAds->user_id = $user->id;
                    $newAds->gift_card_id = $request->gift_card_id;
                    $newAds->payment_currency_type = $request->payment_currency_type;
                    $newAds->currency_type = $request->currency_type;
                    $newAds->price = $request->price;
                    $newAds->amount = $giftCardDetails->amount;
                    $newAds->terms_condition = $request->terms_condition;
                    $newAds->country = json_encode($request->country);
                    $newAds->time_limit = $request->time_limit;
                    $newAds->auto_reply = $request->auto_reply;
                    $newAds->user_registered_before = $request->user_registered_before;
                    $newAds->payment_method = json_encode($request->payment_method);
                    $newAds->status = $request->status;
                    $newAds->save();

                    $giftCardDetails->status = GIFT_CARD_STATUS_TRADING;
                    $giftCardDetails->save();

                    $response = responseData(true, __('Gift Card is created successfully!'));
                } else {
                    $response = responseData(false, __('You have already a ads for this gift card!'));
                }
            } else {
                $response = responseData(false, __('Gift card not found!'));
            }
        } catch (\Exception $e) {
            storeException('storeGiftCardAdds', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
        return $response;
    }

    public function updateGiftCardAdds($request)
    {
        $user = auth()->user();

        try {
            $checkAds = PGiftCard::where('user_id', $user->id)
                ->where('uid', $request->uid)
                ->first();

            if (isset($checkAds)) {
                $checkAds->payment_currency_type = $request->payment_currency_type;
                $checkAds->currency_type = $request->currency_type;
                $checkAds->price = $request->price;
                $checkAds->terms_condition = $request->terms_condition;
                $checkAds->country = json_encode($request->country);
                $checkAds->time_limit = $request->time_limit;
                $checkAds->auto_reply = $request->auto_reply;
                $checkAds->user_registered_before = $request->user_registered_before;
                if (isset($request->payment_method))
                    $checkAds->payment_method = json_encode($request->payment_method);
                $checkAds->status = $request->status;
                $checkAds->save();

                $response = responseData(true, __('Gift Card is updated successfully!'));
            } else {
                $response = responseData(false, __('Invalid gift card update request!'));
            }
        } catch (\Exception $e) {
            storeException('storeGiftCardAdds', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }

        return $response;
    }

    public function giftCardDetails($request)
    {
        if (isset($request->uid)) {
            $giftCardDetails = PGiftCard::with([
                'gift_card:id,uid,gift_card_banner_id,coin_type,amount',
                'gift_card.banner:uid,title,sub_title,banner'
            ])
                ->where('uid', $request->uid)
                ->where('user_id', getUserId())
                ->first();
            if ($giftCardDetails) {
                $giftCardDetails->gift_card->banner->banner = isset($giftCardDetails->gift_card->banner->banner) ? asset(GIFT_CARD_BANNER . $giftCardDetails->gift_card->banner->banner) : null;
                $response = responseData(true, __('Gift card details!'), $giftCardDetails);
            } else
                $response = responseData(false, __('Gift card details not found!'));

        } else {
            $response = responseData(false, __('Gift card Uid is required!'));
        }

        return $response;
    }

    public function giftCardDelete($request)
    {
        if (isset($request->gift_card_id)) {
            $user = auth()->guard('api')->user();

            $giftAdsDetails = PGiftCard::where('id', $request->gift_card_id)
                ->where('user_id', $user->id)
                ->first();
            if (isset($giftAdsDetails)) {
                $giftCardOrder = PGiftCardOrder::where('p_gift_card_id', $giftAdsDetails->id)->first();

                if (isset($giftCardOrder)) {
                    $response = responseData(false, __('You can not delete this gift card ads because, this has already a order!'));
                } else {
                    DB::beginTransaction();
                    if ($card = GiftCard::where('id', $giftAdsDetails->gift_card_id)->where('user_id', $user->id)->whereStatus(GIFT_CARD_STATUS_TRADING)->first()) {
                        if ($giftAdsDetails->delete() && $card->update(['status' => GIFT_CARD_STATUS_ACTIVE])) {
                            DB::commit();
                            $response = responseData(true, __('Gift card ads is deleted successfully!'));
                        } else {
                            $response = responseData(false, __('Gift card ads failed to delete'));
                        }
                    } else {
                        $response = responseData(false, __('Gift card not found!'));
                    }
                }

            } else {
                $response = responseData(false, __('Invalid Request to delete gift card!'));
            }

        } else {
            $response = responseData(false, __('Enter P2P gift card ads id!'));
        }
        return $response;
    }

    public function statusChangeGiftCardAds($request)
    {
        if (isset($request->id)) {
            $user = auth()->user();

            $giftAdsDetails = PGiftCard::where('uid', $request->uid)
                ->where('user_id', $user->id)
                ->first();
            if (isset($giftAdsDetails)) {
                if (in_array($giftAdsDetails->status, [GIFT_CARD_INACTIVE, GIFT_CARD_ACTIVE])) {
                    $giftAdsDetails->status = ($giftAdsDetails->status == GIFT_CARD_ACTIVE) ? GIFT_CARD_ACTIVE : GIFT_CARD_INACTIVE;
                    $giftAdsDetails->save();

                    $response = responseData(true, __('Status is updated successfully!'));
                } else {
                    $response = responseData(false, __('You can not change the status of this giftcard!'));
                }
            } else {
                $response = responseData(false, __('Gift card is not found!'));
            }

        } else {
            $response = responseData(false, __('Enter Gift card ID'));
        }
        return $response;
    }

    public function userGiftCardAdsList($request)
    {
        $limit = isset($request->limit) ? $request->limit : 25;
        $offset = isset($request->page) ? $request->page : 1;
        $user = auth()->user();

        $giftAdsList = PGiftCard::with(['gift_card:id,coin_type'])
            ->where('user_id', $user->id)
            ->when(isset($request->payment_type), function ($query) use ($request) {
                $query->where('payment_currency_type', $request->payment_type);
            })
            ->when(isset($request->payment_currency), function ($query) use ($request) {
                $query->where('currency_type', $request->payment_currency);
            })
            ->when(isset($request->status) && $request->status !== 'all', function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()->paginate($limit, ['*'], 'page', $offset);

        $giftAdsList->map(function ($query) {
            $query->status_name = getGiftCardAdStatus($query->status);
        });

        $response = responseData(true, __('Gift Card ads list'), $giftAdsList);

        return $response;
    }

    public function allGiftCardAdsList($request)
    {
        $limit = isset($request->limit) ? $request->limit : 25;
        $offset = isset($request->page) ? $request->page : 1;

        $giftAdsList = PGiftCard::with(['user', 'gift_card'])->where('status', GIFT_CARD_ACTIVE)
            ->when(
                isset($request->payment_currency_type),
                function ($query) use ($request) {
                    $query->where('payment_currency_type', $request->payment_currency_type);
                }
            )
            ->when(
                isset($request->payment_currency_type) && isset($request->currency_type),
                function ($query) use ($request) {
                    $query->where('currency_type', $request->currency_type);
                }
            )
            ->when(
                isset($request->price) && is_numeric($request->price),
                function ($query) use ($request) {
                    $query->where('price', '<=', $request->price);
                }
            )
            ->when(
                isset($request->payment_method),
                function ($query) use ($request) {
                    $query->where('payment_method', 'LIKE', "%{$request->payment_method}%");
                }
            )
            ->when(
                isset($request->country),
                function ($query) use ($request) {
                    $query->where('country', 'LIKE', "%{$request->country}%");
                }
            )
            ->latest()->paginate($limit, ['*'], 'page', $offset);
        $giftAdsList->map(function ($card) {
            $card->price = $card->price . ' ' . $card->currency_type;
            $card->amount = $card->amount . ' ' . $card->gift_card->coin_type;
            $card->coin_type = $card->gift_card->coin_type;
            $card->user->photo = imageSrcUser($card->user->photo ?? '', IMG_USER_VIEW_PATH);
            if ($payment_method = json_decode($card->payment_method, true)) {
                $card->payment_methods = BankRecord::getBankFormRelation()->whereIn('id', $payment_method)->get();
                $card->payment_methods?->map(function($item){
                    if ($item->bank && isset($item->bank)) {
                        $bank = json_decode($item->bank, true);
                        if(json_last_error() !== JSON_ERROR_NONE || !is_array($bank)) $bank = [];
                            $item->bank = $bank;
                    }
                    return $item;
                });
            } else
                $card->payment_methods = null;

        });
        $response = responseData(true, __('All Gift Card ads list'), $giftAdsList);

        return $response;
    }

    public function placeGiftCardOrder($request)
    {
        try {
            $giftCardDetails = PGiftCard::where('id', $request->gift_card_id)
                ->where('status', GIFT_CARD_ACTIVE)
                ->first();
            DB::beginTransaction();
            if (isset($giftCardDetails)) {
                $user = auth()->user();

                $card = GiftCard::find($giftCardDetails->gift_card_id);
                $payment_type = $giftCardDetails->payment_currency_type;

                if ($payment_type == PAYMENT_CURRENCY_CRYPTO) {
                    if (!$wallet = P2PWallet::where(['user_id' => $user->id, 'coin_type' => $card->coin_type ?? ""])->first())
                        return responseData(false, __('Your wallet not found'));
                }

                if ($payment_type == PAYMENT_CURRENCY_FIAT) {
                    if (!isset($request->payment_method_uid))
                        return responseData(false, __('Payment method is required'));

                    if (!$paymentMethod = BankRecord::getOnlyP2p()->where('id', $request->payment_method_uid)->first())
                        return responseData(false, __('Payment method not found'));
                }

                $checkGiftCardOrder = PGiftCardOrder::where('p_gift_card_id', $giftCardDetails->id)
                    ->whereNotIn('status', [TRADE_STATUS_CANCELED_TIME_EXPIRED, TRADE_STATUS_CANCELED])
                    ->first();

                if (isset($checkGiftCardOrder)) {
                    $response = responseData(false, __('Gift card ads already sold, you can not buy this!'));
                    return $response;
                }

                if ($giftCardDetails->user_id == $user->id) {
                    $response = responseData(false, __('You can not buy self Gift Card!'));
                    return $response;
                }

                $checkValidation = $this->checkValidation($giftCardDetails, $user);
                if (!$checkValidation['success']) {
                    return $checkValidation;
                }


                $seller_id = $giftCardDetails->user_id;
                $buyer_id = $user->id;


                $giftCardOrderPlace = new PGiftCardOrder;
                $giftCardOrderPlace->uid = pMakeUniqueId();
                $giftCardOrderPlace->seller_id = $seller_id;
                $giftCardOrderPlace->buyer_id = $buyer_id;
                $giftCardOrderPlace->p_gift_card_id = $giftCardDetails->id;
                $giftCardOrderPlace->payment_currency_type = $giftCardDetails->payment_currency_type;
                $giftCardOrderPlace->currency_type = $giftCardDetails->currency_type;
                $giftCardOrderPlace->price = $giftCardDetails->price;
                $giftCardOrderPlace->amount = $giftCardDetails->amount;
                $giftCardOrderPlace->payment_time = $giftCardDetails->time_limit;
                $giftCardOrderPlace->order_id = '#000000000' . (PGiftCardOrder::get()->count() + 1);

                if (!empty($giftCardDetails->time_limit)) {
                    $payment_expire_time = Carbon::now();
                    $giftCardOrderPlace->payment_expired_time = $payment_expire_time->addMinutes($giftCardDetails->time_limit)->format('Y-m-d H:i:s');
                }

                $giftCardOrderPlace->payment_method_id = $request->payment_method_uid;
                $giftCardOrderPlace->status = TRADE_STATUS_ESCROW;
                $giftCardOrderPlace->is_queue = STATUS_ACTIVE;

                $giftCardOrderPlace->save();

                if (isset($giftCardDetails->auto_reply)) {
                    $conversation = new PGiftCardChat;
                    $conversation->sender_id = $user->id;
                    $conversation->receiver_id = $giftCardDetails->user_id;
                    $conversation->messaage = $giftCardDetails->auto_reply;
                    $conversation->save();
                }

                $giftCardDetails->status = GIFT_CARD_ONGOING;
                $giftCardDetails->save();

                DB::commit();
                $response = responseData(true, __('Order Placed successfully!'), [
                    'order_uid' => $giftCardOrderPlace->uid
                ]);
            } else {
                $response = responseData(false, __('Gift Card is not found!'));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('placeGiftCardOrder', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }

        return $response;
    }

    public function payNowGiftCardOrder($request)
    {
        try {
            if (isset($request->gift_card_order_id)) {
                $user = auth()->user();

                $giftCardOrderDetails = PGiftCardOrder::where('id', $request->gift_card_order_id)
                    ->where('buyer_id', $user->id)
                    ->first();
                if (isset($giftCardOrderDetails)) {
                    $payment_expire_time = Carbon::now()->format('Y-m-d H:i:s');

                    if (($giftCardOrderDetails->payment_time > 0) && ($giftCardOrderDetails->payment_expired_time < $payment_expire_time)) {
                        $response = responseData(false, __('Time is expired you can not payment now!'));
                        return $response;
                    }

                    if ($giftCardOrderDetails->payment_currency_type == PAYMENT_CURRENCY_CRYPTO) {
                        $userWallet = P2PWallet::where('user_id', $user->id)
                            ->where('coin_type', $giftCardOrderDetails->currency_type)
                            ->where('status', STATUS_ACTIVE)
                            ->first();

                        if (isset($userWallet)) {
                            $sellerWallet = P2PWallet::where('user_id', $giftCardOrderDetails->seller_id)
                                ->where('coin_type', $giftCardOrderDetails->currency_type)
                                ->where('status', STATUS_ACTIVE)
                                ->first();

                            if (!isset($sellerWallet)) {
                                $response = responseData(false, __('Seller Wallet is not found!'));
                                return $response;
                            }

                            if ($userWallet->balance >= $giftCardOrderDetails->amount) {
                                $userWallet->balance -= $giftCardOrderDetails->price;
                                $userWallet->save();

                                $sellerWallet->balance += $giftCardOrderDetails->price;
                                $sellerWallet->save();

                                $giftCardOrderDetails->payment_status = STATUS_ACTIVE;
                                $giftCardOrderDetails->status = TRADE_STATUS_PAYMENT_DONE;
                                $giftCardOrderDetails->save();
                                $this->sendDataViaWebsocket($giftCardOrderDetails->seller_id, $giftCardOrderDetails->uid, ['order' => $giftCardOrderDetails, 'message' => __("Buyer sent payment")]);
                                $response = responseData(true, __('Payment is done successfully!'));
                            } else {
                                $response = responseData(false, __('Wallet has not sufficent balance!'));
                            }
                        } else {
                            $response = responseData(false, __('Wallet not found!'));
                        }
                    } else {
                        if (!isset($request->slip))
                            return responseData(false, __('Payment slip is required!'));

                        if ($request->hasFile('slip')) {
                            if (isset($giftCardOrderDetails->payment_sleep))
                                deleteFile(public_path(IMG_PATH), $giftCardOrderDetails->payment_sleep);
                            $image = uploadFile($request->file('slip'), IMG_PATH);
                            $giftCardOrderDetails->payment_sleep = $image;

                            $giftCardOrderDetails->payment_status = STATUS_ACTIVE;
                            $giftCardOrderDetails->status = TRADE_STATUS_PAYMENT_DONE;
                            $giftCardOrderDetails->save();
                            $this->sendDataViaWebsocket($giftCardOrderDetails->seller_id, $giftCardOrderDetails->uid, ['order' => $giftCardOrderDetails, 'message' => __("Buyer sent payment")]);
                            return responseData(true, __('Payment is done successfully!'));
                        }
                        return responseData(false, __('Payment slip not found!'));
                    }
                } else {
                    $response = responseData(false, __('Invalid request to payment!'));
                }

            } else {
                $response = responseData(false, __('Enter gift card order id!'));
            }

            return $response;
        } catch (\Exception $e) {
            storeException('payNowGiftCardOrder', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    public function paymentConfirmGiftCardOrder($request)
    {
        try {
            if (isset($request->gift_card_order_id)) {
                $user = auth()->user();

                $giftCardOrderDetails = PGiftCardOrder::where('id', $request->gift_card_order_id)
                    ->where('seller_id', $user->id)
                    ->first();
                if (isset($giftCardOrderDetails)) {
                    DB::beginTransaction();
                    if ($ads = PGiftCard::where('id', $giftCardOrderDetails->p_gift_card_id)->first()) {
                        $giftCardOrderDetails->status = TRADE_STATUS_TRANSFER_DONE;
                        if ($giftCardOrderDetails->save() && $ads->update(['status' => GIFT_CARD_SUCCESS])) {
                            $card = GiftCard::find($ads->gift_card_id);
                            $data = [
                                'uid' => uniqid() . date('') . time(),
                                'gift_card_banner_id' => $card->gift_card_banner_id,
                                'coin_type' => $card->coin_type,
                                'wallet_type' => $card->wallet_type,
                                'amount' => $card->amount,
                                'fees' => $card->fees,
                                'redeem_code' => date('') . time() . rand(11111, 99999),
                                'user_id' => $giftCardOrderDetails->buyer_id,
                                'owner_id' => $giftCardOrderDetails->buyer_id,
                                'status' => GIFT_CARD_STATUS_ACTIVE
                            ];
                            $gift_card = GiftCard::create($data);
                            $card->update(['status' => GIFT_CARD_STATUS_TRANSFARED]);
                            DB::commit();
                            $this->sendDataViaWebsocket($giftCardOrderDetails->seller_id, $giftCardOrderDetails->uid, ['order' => $giftCardOrderDetails, 'message' => __("Seller released gift card")]);
                            $response = responseData(true, __('Payment is confirmed successfully!'));
                        } else {
                            $response = responseData(false, __('Payment is failed to confirm!'));
                        }
                    } else {
                        $response = responseData(false, __('This order\'s advertisement not found!'));
                    }

                } else {
                    $response = responseData(false, __('Invalid request to payment confirm!'));
                }

            } else {
                $response = responseData(false, __('Enter gift card order id!'));
            }

            return $response;
        } catch (\Exception $e) {
            storeException('paymentConfirmGiftCardOrder', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    public function cancelGiftCardOrder($request)
    {
        try {
            if (isset($request->gift_card_order_id)) {
                $user = auth()->user();

                $giftCardOrderDetails = PGiftCardOrder::where('id', $request->gift_card_order_id)
                    // ->where('seller_id', $user->id)
                    ->first();
                if (isset($giftCardOrderDetails)) {
                    $ads = PGiftCard::find($giftCardOrderDetails->p_gift_card_id);
                    $ads->status = GIFT_CARD_ACTIVE;
                    $ads->save();
                    $giftCardOrderDetails->status = TRADE_STATUS_CANCELED;
                    $giftCardOrderDetails->save();

                    $response = responseData(true, __('Order is canceled successfully!'));

                } else {
                    $response = responseData(false, __('Order not found!'));
                }

            } else {
                $response = responseData(false, __('Enter gift card order id!'));
            }

            return $response;
        } catch (\Exception $e) {
            storeException('paymentConfirmGiftCardOrder', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    public function checkValidation($giftCardDetails, $user)
    {
        if (isset($giftCardDetails->user_registered_before)) {
            $daysSinceCreated = Carbon::parse($user->created_at)->diffInDays(Carbon::now());

            if ($giftCardDetails->user_registered_before > $daysSinceCreated) {
                $response = ['success' => false, 'message' => __('Your account registration must have to ') . $giftCardDetails->user_registered_before . __(' days older')];
                return $response;
            }
        }

        $response = responseData(true, __('Check validation success'));

        return $response;
    }

    public function sendMessage($request)
    {
        $response = responseData(false, __('Something went wrong'));
        try {
            $giftCardOrder = PGiftCardOrder::where('id', $request->gift_card_order_id)->first();
            if (isset($giftCardOrder)) {
                $channel_id3 = '';
                $dispute = '';
                $assignedAdmin = NULL;
                if ($giftCardOrder->is_reported) {
                    $dispute = PGiftCardOrderDisputes::where('gift_card_order_id', $giftCardOrder->id)->first();
                    if (!empty($dispute)) {
                        $assignedAdmin = $dispute->assigned_admin ?? NULL;
                    }
                }
                $user = auth()->user();
                $sender_id = $user->id;

                $conversation = new PGiftCardChat;
                if (!empty($dispute)) {
                    $conversation->dispute_id = $dispute->id;
                    if ($user->role == USER_ROLE_ADMIN) {
                        $receiver_id = $giftCardOrder->buyer_id;
                        $channel_id2 = $receiver_id . '-' . $giftCardOrder->uid;
                        $channel_id3 = $giftCardOrder->seller_id . '-' . $giftCardOrder->uid;
                    } else {
                        $receiver_id = ($sender_id == $giftCardOrder->buyer_id) ? $giftCardOrder->seller_id : $giftCardOrder->buyer_id;
                        $channel_id2 = $receiver_id . '-' . $giftCardOrder->uid;
                        if (!empty($assignedAdmin)) {
                            $channel_id3 = $assignedAdmin . '-' . $giftCardOrder->uid;
                        }
                    }
                } else {
                    $receiver_id = ($sender_id == $giftCardOrder->buyer_id) ? $giftCardOrder->seller_id : $giftCardOrder->buyer_id;
                    $channel_id2 = $receiver_id . '-' . $giftCardOrder->uid;
                }

                $conversation->sender_id = $sender_id;
                $conversation->receiver_id = $receiver_id;
                $conversation->gift_card_order_id = $giftCardOrder->id;
                $conversation->message = $request->message;

                if ($request->hasFile('file')) {
                    $imageName = uploadAnyFileP2P($request->file, CONVERSATION_ATTACHMENT_PATH);
                    $conversation->file = $imageName;
                }
                $conversation->save();

                $data['user'] = $user;
                $data['conversation'] = $conversation;
                $data['conversation']['sender_id'] = $sender_id;
                $data['conversation']['receiver_id'] = $receiver_id;
                $response = ['success' => true, 'message' => __('Message is sent successfully'), 'data' => getChatDataP2P($data)];
                $channel_id = $sender_id . '-' . $giftCardOrder->uid;

                $channel_name = 'New-Message-' . $channel_id;
                $channel_name2 = 'New-Message-' . $channel_id2;
                $event_name = 'Conversation';
                $channel_data = $response;
                sendDataThroughWebSocket($channel_name, $event_name, $channel_data);
                sendDataThroughWebSocket($channel_name2, $event_name, $channel_data);
                if (!empty($channel_id3)) {
                    $channel_name3 = 'New-Message-' . $channel_id3;
                    sendDataThroughWebSocket($channel_name3, $event_name, $channel_data);
                }

            } else {
                $response = responseData(false, __('Order not found!'));
            }
        } catch (\Exception $e) {
            storeException('sendMessage Gift Card order ex', $e->getMessage());
            $response = responseData(false, __('Something went wrong!'));
        }

        return $response;
    }

    public function disputeOrderProcess($request)
    {
        $user = auth()->user();
        try {

            $giftCardOrderDetails = PGiftCardOrder::where('id', $request->gift_card_order_id)
                ->first();

            if (isset($giftCardOrderDetails)) {
                $checkDisputeValidation = $this->checkDisputeOrderValidation($giftCardOrderDetails, $user);
                if ($checkDisputeValidation['success']) {
                    $giftCardOrderDetails->is_reported = STATUS_ACTIVE;
                    $giftCardOrderDetails->reported_user = $user->id;
                    $giftCardOrderDetails->save();

                    $reported_user = ($giftCardOrderDetails->buyer_id == $user->id) ? $giftCardOrderDetails->seller_id : $giftCardOrderDetails->buyer_id;
                    $newDispute = new PGiftCardOrderDisputes;
                    $newDispute->uid = pMakeUniqueId();
                    $newDispute->gift_card_order_id = $giftCardOrderDetails->id;
                    $newDispute->user_id = $user->id;
                    $newDispute->reported_user = $reported_user;
                    $newDispute->reason_heading = $request->reason_subject;
                    $newDispute->details = $request->reason_details;

                    if ($request->hasFile('image')) {

                        $imageName = uploadAnyFileP2P($request->image, PAYMENT_SLIP_PATH);
                        $newDispute->image = $imageName;

                    }

                    $newDispute->save();
                    $this->sendDataViaWebsocket($newDispute->user_id, $giftCardOrderDetails->uid, ['order' => $giftCardOrderDetails, 'message' => __("This order has been reported")], $newDispute->reported_user);
                    $response = responseData(true, __('Dispute is created successfully!'));

                } else {
                    return $checkDisputeValidation;
                }

            } else {
                $response = responseData(false, __('Gift Card order is not found!'));
            }

        } catch (\Exception $e) {
            storeException('sendMessage Gift Card order ex', $e->getMessage());
            $response = responseData(false, __('Something went wrong!'));
        }

        return $response;
    }

    public function checkDisputeOrderValidation($giftCardOrderDetails, $user)
    {
        if ($giftCardOrderDetails->status == TRADE_STATUS_PAYMENT_DONE || $giftCardOrderDetails->status == TRADE_STATUS_TRANSFER_DONE) {
            if ($giftCardOrderDetails->is_reported == 0) {
                $response = ['success' => true, 'message' => __('You can submit a dispute request for this order!')];
            } else {

                if ($giftCardOrderDetails->reported_user == $user->id) {
                    $response = ['success' => false, 'message' => __('You already submited a dispute request!')];
                } else {
                    if ($giftCardOrderDetails->reported_user == $giftCardOrderDetails->buyer_id) {
                        $response = ['success' => false, 'message' => __('Buyer already submited a dispute request!')];
                    } else {
                        $response = ['success' => false, 'message' => __('Seller already submited a dispute request!')];
                    }
                }
            }
        } else {
            $response = ['success' => false, 'message' => __('You can dispute the order after payment done or transfer done')];
        }

        return $response;
    }

    public function getOrderDisputedDetails($uid)
    {
        $orderDisputeDetails = PGiftCardOrder::with(['buyer', 'seller', 'dispute_details', 'reporting_user'])->where('uid', $uid)->first();

        if (isset($orderDisputeDetails)) {
            $response = responseData(true, __('Dispute order details'), $orderDisputeDetails);
        } else {
            $response = responseData(false, __('Invalid Request'));
        }

        return $response;
    }

    public function getConversationListForDisputeOrder($order_id, $dispute_id)
    {
        $conversation_list = PGiftCardChat::where('gift_card_order_id', $order_id)
            ->where('dispute_id', $dispute_id)
            ->with(['receiver', 'user'])->get();
        $response = ['success' => true, 'message' => __('Dispute order conversation list'), 'data' => $conversation_list];

        return $response;
    }

    public function refundDisputeDetailsByAdmin($request)
    {
        $response = responseData(false, __('Something went wrong'));
        DB::beginTransaction();
        try {
            $user = auth()->user();
            if (!isset($request->dispute_uid))
                return responseData(false, __('Disput uid is required'));

            $dispute_details = PGiftCardOrderDisputes::where('uid', $request->dispute_uid)
                ->where('assigned_admin', $user->id)->first();


            if (isset($dispute_details)) {
                $orderDetails = PGiftCardOrder::find($dispute_details->gift_card_order_id);

                if (isset($orderDetails)) {
                    if ($orderDetails->payment_currency_type == PAYMENT_CURRENCY_FIAT) {
                        $ads = PGiftCard::find($orderDetails->p_gift_card_id);
                        $ads->status = GIFT_CARD_ACTIVE;
                        $ads->save();

                        $orderDetails->status = TRADE_STATUS_REFUNDED_BY_ADMIN;
                        $orderDetails->save();

                        $dispute_details->status = STATUS_ACCEPTED;
                        $dispute_details->save();

                        $response = ['success' => true, 'message' => __('Order is refunded successfully')];
                    } else {
                        $response = responseData(false, __('Refund not allow for crypto payed order!'));
                    }
                } else {
                    $response = responseData(false, __('Gift Card Order is not found!'));
                }

            } else {
                $response = responseData(false, __('Dispute Order not found'));
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('releaseDisputeDetailsByAdmin ex', $e->getMessage());
        }
        return $response;
    }

    public function releaseDisputeDetailsByAdmin($request)
    {
        $response = responseData(false, __('Something went wrong'));
        DB::beginTransaction();
        try {
            $user = auth()->user();
            if (empty($request->dispute_uid)) {
                return responseData(false, __('Something is missing!'));
            }
            $dispute_details = PGiftCardOrderDisputes::where('uid', $request->dispute_uid)
                ->where('assigned_admin', $user->id)->first();

            if (isset($dispute_details)) {
                GiftCardReleaseDisputeOrderJob::dispatch($dispute_details);
                $response = ['success' => true, 'message' => __('Order is released successfully')];
            } else {
                $response = responseData(false, __('Dispute Order not found'));
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('releaseDisputeDetailsByAdmin ex', $e->getMessage());
        }
        return $response;
    }

    public function assignDisputeDetailsToAdmin($request)
    {
        $response = ['success' => false, 'message' => __('Something went wrong')];
        try {
            $admin_details = User::where('id', $request->employee_id)->where('role', USER_ROLE_ADMIN)->where('status', STATUS_ACTIVE)->first();

            $dispute_details = PGiftCardOrderDisputes::where('uid', $request->dispute_uid)->first();

            if (isset($admin_details) && isset($dispute_details)) {
                if (isset($dispute_details->assigned_admin)) {
                    $response = ['success' => false, 'message' => __('This is already assigned, You can not re-assigned it!')];
                } else {
                    $dispute_details->assigned_admin = $admin_details->id;
                    $dispute_details->updated_by = auth()->user()->id;
                    $dispute_details->save();

                    PGiftCardChat::create([
                        'gift_card_order_id' => $dispute_details->gift_card_order_id,
                        'dispute_id' => $dispute_details->id,
                        'sender_id' => $admin_details->id,
                        'receiver_id' => $dispute_details->user_id,
                        'message' => 'hello from support, and i am assigned to help you. please co-operate me. thanks'
                    ]);

                    $response = ['success' => true, 'message' => __('Dispute details is assigned successfully!')];
                }

            } else {
                $response = ['success' => false, 'message' => __('Invalid Request!')];
            }
        } catch (\Exception $e) {
            storeException('assignDisputeDetailsToAdmin ex', $e->getMessage());
        }

        return $response;
    }

    public function getGiftCardPageData()
    {
        try {
            $data = [];
            $data['assets'] = DB::table('coins')->join('p_coin_settings', 'coins.coin_type', 'p_coin_settings.coin_type')
                ->where('coins.status', STATUS_ACTIVE)->where('p_coin_settings.trade_status', STATUS_ACTIVE)
                ->get(['p_coin_settings.coin_type']);
            $data['currency'] = DB::table('currency_lists')->join('p_currency_settings', 'currency_lists.code', 'p_currency_settings.currency_code')
                ->where('currency_lists.status', STATUS_ACTIVE)->where('p_currency_settings.trade_status', STATUS_ACTIVE)
                ->get(['p_currency_settings.currency_code']);
            $paymentMethod = BankRecord::getUserPaymentMethod(authUserId_p2p() ?? 0)->get();
            $paymentMethod->map(function($item){
                if ($item->bank && isset($item->bank)) {
                    $bank = json_decode($item->bank, true);
                    if(json_last_error() !== JSON_ERROR_NONE || !is_array($bank)) $bank = [];
                        $item->bank = $bank;
                }
                return $item;
            });
            $data['payment_method'] = $paymentMethod;
            $data['is_payment_method_available'] = $paymentMethod->count() > 0;
            $data['payment_time'] = PPaymentTime::where('status', STATUS_ACTIVE)->get(['time']);
            $data['country'] = CountryList::where('status', STATUS_ACTIVE)->get(['key', 'value']);
            // $data['counterparty'] = filter_var(settings(['counterparty_condition'])['counterparty_condition'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $data['assets']->map(function ($coin) {
                $coin->label = $coin->coin_type;
                $coin->value = $coin->coin_type;
            });
            $data['currency']->map(function ($currency) {
                $currency->label = $currency->currency_code;
                $currency->value = $currency->currency_code;
            });
            $data['payment_time']->map(function ($paymentTime) {
                $paymentTime->label = $paymentTime->time;
                $paymentTime->value = $paymentTime->time;
            });
            $data['country']->map(function ($country) {
                $country->label = $country->value;
                $country->value = $country->key;
            });
            $data['payment_method']->map(function ($method) {
                $method->label = $method->adminPamyntMethod->name ?? "";
                $method->value = $method->uid;
            });
            return responseData(true, __("Ads setting get successfully"), $data);
        } catch (\Exception $e) {
            storeException('getGiftCardPageData', $e->getMessage());
            return responseData(false, $e->getMessage());
        }
    }

    public function getGiftCardData($request)
    {
        try {
            $limit = $request->limit ?? 10;
            $cards = GiftCard::select(['id', 'uid', 'coin_type', 'wallet_type', 'user_id', 'redeem_code', 'amount', 'note', 'owner_id', 'is_ads_created', 'status', 'created_at', 'gift_card_banner_id'])
                ->whereStatus(GIFT_CARD_STATUS_ACTIVE)
                ->where('lock', '<>', STATUS_ACTIVE)
                ->where('user_id', getUserId())
                ->with(['banner:uid,title,sub_title,banner,category_id', 'banner.category:uid,name'])
                ->orderBy('created_at', 'DESC')
                ->paginate($limit);

            $cards->map(function ($card) {
                $card->wallet_type = getWalletGiftCard($card->wallet_type);
                $card->_status = ($card->status == 1 ? ($card->lock == 1 ? 0 : 1) : 0);
                $card->_lock = $card->lock ? 1 : 0;
                $card->lock_status = $card->status;
                $card->status = getStatusGiftCard($card->status);
                $card->lock = $card->lock ? __("Locked") : __("Unlocked");
                $card->banner->image = isset($card->banner->banner) ? asset(GIFT_CARD_BANNER . $card->banner->banner) : null;
            });
            return responseData(true, __("Gift cards get successfully"), $cards);
        } catch (\Exception $e) {
            storeException('getGiftCardData', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function getGiftCardAdsDetails($request)
    {
        if (!isset($request->uid))
            return responseData(false, __('Gift card Uid is required!'));

        if (
            $ads = PGiftCard::with([
                'user:id,first_name,last_name,nickname,photo',
                'gift_card:id,uid,coin_type,amount,gift_card_banner_id',
                'gift_card.banner:uid,title,sub_title,banner'
            ])->where('uid', $request->uid)
                ->first(['id', 'uid', 'user_id', 'gift_card_id', 'payment_currency_type', 'currency_type', 'price', 'terms_condition', 'time_limit', 'payment_method'])
        ) {
            $ads->user->photo = imageSrcUser($ads->user->photo ?? '', IMG_USER_VIEW_PATH);
            $ads->gift_card->banner->banner = isset($ads->gift_card->banner->banner) ? asset(GIFT_CARD_BANNER . $ads->gift_card->banner->banner) : null;
            if ($payment_method = json_decode($ads->payment_method, true)) {
                $ads->payment_methods = BankRecord::getBankFormRelation()->whereIn('id', $payment_method)->get();
                $ads->payment_methods->map(function($item){
                    if ($item->bank && isset($item->bank)) {
                        $bank = json_decode($item->bank, true);
                        if(json_last_error() !== JSON_ERROR_NONE || !is_array($bank)) $bank = [];
                            $item->bank = $bank;
                    }
                    return $item;
                });
            } else
                $ads->payment_methods = null;
            return responseData(true, __('Gift card advertisement details found!'), $ads);
        }
        return responseData(false, __('Gift card advertisement details not found!'));
    }

    public function filterGiftCardAds($request)
    {
        try {
            $limit = $request->limit ?? 20;
            $ads = PGiftCard::with(['user', 'gift_card'])
                ->when(
                    isset($request->payment_currency_type),
                    function ($query) use ($request) {
                        $query->where('payment_currency_type', $request->payment_currency_type);
                    }
                )
                ->when(
                    isset($request->payment_currency_type) && isset($request->currency_type),
                    function ($query) use ($request) {
                        $query->where('currency_type', $request->currency_type);
                    }
                )
                ->when(
                    isset($request->price) && is_numeric($request->price),
                    function ($query) use ($request) {
                        $query->where('price', '<=', $request->price);
                    }
                )
                ->when(
                    isset($request->payment_method),
                    function ($query) use ($request) {
                        $query->where('payment_method', 'LIKE', "%{$request->payment_method}%");
                    }
                )
                ->when(
                    isset($request->country),
                    function ($query) use ($request) {
                        $query->where('country', 'LIKE', "%{$request->country}%");
                    }
                )
                ->orderBy('created_at', 'DESC')->paginate($limit);
            $ads->map(function ($card) {
                $card->price = $card->price . ' ' . $card->currency_type;
                $card->amount = $card->amount . ' ' . $card->gift_card->coin_type;
                $card->coin_type = $card->gift_card->coin_type;
                $card->user->photo = imageSrcUser($card->user->photo ?? '', IMG_USER_VIEW_PATH);
                if ($payment_method = json_decode($card->payment_method, true)) {
                    $card->payment_methods = BankRecord::getBankFormRelation()->whereIn('id', $payment_method)->get();
                } else
                    $card->payment_methods = null;

            });
            return responseData(true, __("Filter advertisements get successfully"), $ads);
        } catch (\Exception $e) {
            storeException('filterGiftCardAds', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function getGiftCardOrder($request)
    {
        try {
            $data = [];

            if (!isset($request->order_uid))
                return responseData(false, __("Order uid is required"));

            if ($order = PGiftCardOrder::with(['p_gift_card', 'p_gift_card.gift_card'])->where('uid', $request->order_uid)->first()) {
                $data['order'] = $order;
                $deposit = null;
                $due_minute = null;
                $authUser = auth()->user() ?? auth()->guard('api')->user();

                if (!$this->checkOrderTime($order)) {
                    $giftCardAds = PGiftCard::find($order->p_gift_card_id);
                    DB::beginTransaction();
                    $giftCardAds->status = GIFT_CARD_ACTIVE;
                    $order->status = TRADE_STATUS_CANCELED_TIME_EXPIRED;

                    $order->save();
                    $giftCardAds->save();
                    DB::commit();
                    return responseData(false, __('This order is closed due to payment time expire'), $data);
                }

                if ($order->buyer_id == $authUser->id) {
                    $data['user_type'] = TRADE_BUYER;
                    $data['user_buyer'] = $authUser;
                    $data['user_seller'] = User::find($order->seller_id);
                    $feedback = PGiftCardFeedback::where(['order_id' => $order->id, 'to_user_id' => $order->buyer_id])->first();
                    $data['order']->feedback = $feedback;
                } else {
                    $data['user_type'] = TRADE_SELLER;
                    $data['user_seller'] = $authUser;
                    $data['user_buyer'] = User::find($order->buyer_id);
                    $feedback = PGiftCardFeedback::where(['order_id' => $order->id, 'to_user_id' => $order->seller_id])->first();
                    $data['order']->feedback = $feedback;
                }

                if ($order->is_reported) {
                    $data['dispute'] = PGiftCardOrderDisputes::with('admin')
                        ->where('gift_card_order_id', $order->id)
                        ->where(
                            fn($q) => $q
                                ->where('user_id', authUserId_p2p())
                                ->orWhere('reported_user', authUserId_p2p())
                        )->first();
                    $data['who_dispute'] = ($data['user_buyer']->id == $data['dispute']->reported_user) ? __("buyer") : __("seller");
                    $data['dispute']->assigned_admin = $data['dispute']->assigned_admin ? $data['dispute']->admin->first_name . ' ' . $data['dispute']->admin->last_name : NULL;
                }



                if (!empty($data['dispute'])) {
                    $data['chat_messages'] = PGiftCardChat::where('gift_card_order_id', $order?->id)
                        ->where('dispute_id', $data['dispute']->id)->with(['receiver', 'user'])->get();
                } else {
                    $data['chat_messages'] = PGiftCardChat::where('gift_card_order_id', $order?->id)
                        ->where(function ($query) {
                            return $query
                                ->where('sender_id', authUserId_p2p())
                                ->orWhere('receiver_id', authUserId_p2p());
                        })->with(['receiver', 'user'])->get();
                }
                $paymentMethod = BankRecord::getBankFormRelation()->where('id', $order?->payment_method_id)->first();
                if ($paymentMethod?->bank && isset($paymentMethod->bank)) {
                    $bank = json_decode($paymentMethod->bank, true);
                    if(json_last_error() !== JSON_ERROR_NONE || !is_array($bank)) $bank = [];
                        $paymentMethod->bank = $bank;
                }
                $data['payment_methods'] = $paymentMethod;
                $data['chat_messages']->map(function ($q) {
                    $q->file_path = $q->file ? filePathP2P(CONVERSATION_ATTACHMENT_PATH, $q->file) : '';
                    if (isset($q->user)) {
                        $q->sender_image_link = isset($q->user->id) ? showUserImageP2P($q->user->id) : '';
                        $q->user_id = $q->user->id ?? '';
                        $q->user['photo'] = showUserImageP2P($q->user_id);
                    }
                    if (isset($q->receiver)) {
                        $q->receiver_image_link = isset($q->receiver->id) ? showUserImageP2P($q->receiver->id) : '';
                    }
                });

                $data['current_time'] = Carbon::now()->format('Y-m-d H:i:s');
                $data['due_minute'] = getOrderTimeDiff($data['order']);

                return responseData(true, __('Get details page data succesfully'), $data);

            }

            return responseData(false, __("Order not found"));

        } catch (\Exception $e) {
            storeException('getP2pOrderDetails ex', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    private function checkOrderTime($order)
    {
        try {
            if ($order->is_reported == STATUS_PENDING && $order->status == TRADE_STATUS_ESCROW && $order?->payment_time > 0) {
                $orderTimeExpire = Carbon::createFromFormat('Y-m-d H:i:s', $order?->payment_expired_time);
                $currentTime = Carbon::now();

                if ($currentTime->gte($orderTimeExpire))
                    return false;
            }
            return true;
        } catch (\Exception $e) {
            storeException('checkTime gift-card', $e->getMessage());
            return false;
        }
    }

    public function getGiftCardOrdersList($request)
    {
        try {
            $limit = $request->limit ?? 10;
            $orders = PGiftCardOrder::with([
                'p_gift_card:id,gift_card_id',
                'p_gift_card.gift_card:id,coin_type,amount'
            ]);

            if ($request->status == TRADE_STATUS_REPORTED)
                $orders = $orders->where('is_reported', STATUS_ACTIVE)->latest();
            else if ($request->status !== 'all')
                $orders = $orders->where('is_reported', '<>', STATUS_ACTIVE)->where('status', $request->status)->latest();
            else
                $orders = $orders->latest();

            $orders = $orders->paginate($limit);

            $orders->map(function ($query) {
                $query->status_name = gitCardTradeStatusListP2P($query->status);
            });

            return responseData(true, __("Orders get successfully"), $orders);
        } catch (\Exception $e) {
            storeException('checkTime gift-card', $e->getMessage());
            return responseData(true, __("Something went wrong"));
        }
    }

    private function sendDataViaWebsocket($seller_id, $order_id, $data, $buyer_id = null): void
    {
        try {
            $channel_name1 = 'Order-Status-' . ($buyer_id ?? authUserId_p2p()) . '-' . $order_id;
            $channel_name2 = 'Order-Status-' . $seller_id . '-' . $order_id;
            $event_name = 'OrderStatus';
            sendDataThroughWebSocket($channel_name1, $event_name, $data);
            sendDataThroughWebSocket($channel_name2, $event_name, $data);
        } catch (\Exception $e) {
            storeException("sendDataViaWebsocket", $e->getMessage());
        }
    }

    public function getGiftCardTradeHeader()
    {
        $settings = settings(['gift_card_trade_page_header', 'gift_card_trade_page_description', 'gift_card_trade_page_image']);
        // header
        $data['header'] = $settings['gift_card_trade_page_header'] ?? "";
        $data['description'] = $settings['gift_card_trade_page_description'] ?? "";
        $data['banner'] = isset($settings['gift_card_trade_page_image']) ? asset(IMG_PATH . ($settings['gift_card_trade_page_image'])) : null;
        return responseData(true, __('Header details get successfully'), $data);
    }

    public function getGiftCardDetails($gift_card_id)
    {
        try {
            $select = ['id', 'uid', 'gift_card_banner_id', 'coin_type', 'amount'];

            if ($card = GiftCard::with('banner:uid,title,sub_title,banner')->where('id', $gift_card_id)->first($select)) {

                $card->banner->banner = isset($card->banner->banner) ? asset(GIFT_CARD_BANNER . $card->banner->banner) : null;
                return responseData(true, __("Gift card details get successfully"), $card);

            }
            return responseData(false, __('Gift card not found'));
        } catch (\Exception $e) {
            storeException('getGiftCardDetails', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function orderFeedback($request)
    {
        try {
            if ($order = PGiftCardOrder::where('uid', $request->order_uid)->first()) {
                if (!($order->status == TRADE_STATUS_RELEASED_BY_ADMIN || $order->status == TRADE_STATUS_TRANSFER_DONE))
                    return responseData(false, __("Order not complete yet!"));
                $id = authUserId_p2p();
                $updateData = [
                    'order_id' => $order->id,
                    'user_id' => $id,
                    'to_user_id' => $order->seller_id == $id ? $order->buyer_id : $order->seller_id,
                    'feedback' => $request->feedback,
                    'feedback_type' => $request->feedback_type,
                ];

                $updateOrder = [];
                if ($order->seller_id == $id) {
                    if ($order->seller_feedback_type == 1)
                        return responseData(false, __("You already given a feedback"));

                    $updateOrder['seller_feedback_type'] = 1;
                } else {
                    if ($order->buyer_feedback_type == 1)
                        return responseData(false, __("You already given a feedback"));

                    $updateOrder['buyer_feedback_type'] = 1;
                }

                if ($feedback = PGiftCardFeedback::create($updateData)) {
                    $order->update($updateOrder);
                    $order->feedback = PGiftCardFeedback::where(['order_id' => $order->id, 'to_user_id' => $id]);
                    $this->sendDataViaWebsocket($order->seller_id, $order->uid, $order, $order->buyer_id);
                    return responseData(true, __("Feedback updated successfully"), $order);
                }
                return responseData(false, __("Feedback not updated successfully"));
            }
            return responseData(false, __("Order not found"));
        } catch (\Exception $e) {
            storeException("orderFeedback", $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
}
