<?php

use App\Model\Coin;
use App\Model\Wallet;
use Modules\P2P\Entities\P2PWallet;
use Intervention\Image\Facades\Image;
use App\User;
use Carbon\Carbon;
use Modules\P2P\Entities\POrder;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

// Ads Type
const TRADE_BUY_TYPE = 1;
const TRADE_BUYER = 1;
const TRADE_SELL_TYPE = 2;
const TRADE_SELLER = 2;
// Ads Type
const ADS_STATUS_ACTIVE = 1;
const ADS_STATUS_INACTIVE = 0;
// Trade Status
const TRADE_STATUS_CANCELED_TIME_EXPIRED = 0;
const TRADE_STATUS_ESCROW = 1;
const TRADE_STATUS_PAYMENT_DONE = 2;
const TRADE_STATUS_TRANSFER_DONE = 3;
const TRADE_STATUS_CANCELED = 4;
const TRADE_STATUS_REPORTED = 5;
const TRADE_STATUS_REFUNDED_BY_ADMIN = 6;
const TRADE_STATUS_RELEASED_BY_ADMIN = 7;


const WALLET_BALANCE_TRANSFER_SEND = 1;
const WALLET_BALANCE_TRANSFER_RECEIVE = 2;


const PAYMENT_METHOD_LOGO_PATH = 'storage/p2p/payment_method/';
const PAYMENT_SLIP_PATH = 'storage/p2p/payment_slip/';
const CONVERSATION_ATTACHMENT_PATH = 'storage/p2p/conversation/';
const LANDING_PAGE_PATH = 'storage/p2p/landing/';
const LANDING_PAGE_HOW_PATH = 'storage/p2p/landing/';

const TRADE_PRICE_FIXED_TYPE = 1;
const TRADE_PRICE_FLOAT_TYPE = 2;

const PAYMENT_METHOD_BANK = 1;
const PAYMENT_METHOD_MOBILE = 2;
const PAYMENT_METHOD_CARD = 3;

const PAYMENT_METHOD_CARD_TYPE_DEBIT = 1;
const PAYMENT_METHOD_CARD_TYPE_CREDIT = 2;

const KYC_LIST_ARRAY = [
    KYC_PHONE_VERIFICATION => 'p_phone_verification',
    KYC_EMAIL_VERIFICATION => 'p_email_verification',
    KYC_NID_VERIFICATION => 'p_nid_verification',
    KYC_PASSPORT_VERIFICATION => 'p_passport_verification',
    KYC_DRIVING_VERIFICATION => 'p_driving_verification',
    KYC_VOTERS_CARD_VERIFICATION => 'p_voter_verification',
];


const FEEDBACK_TYPE_POSITIVE = 1;
const FEEDBACK_TYPE_NEGATIVE = 0;

const P2P_LANDING_PATH = 'storage/p2p/landing/';

const PAYMENT_CURRENCY_FIAT = 1;
const PAYMENT_CURRENCY_CRYPTO = 2;

const GIFT_CARD_INACTIVE = 0;
const GIFT_CARD_ACTIVE = 1;
const GIFT_CARD_SUCCESS = 2;
const GIFT_CARD_CANCELED = 3;
const GIFT_CARD_ONGOING = 4;

function authUserId_p2p()
{
    return auth()->id() ?? auth()->guard('api')->id();
}
function authUser_p2p()
{
    return auth()->user() ?? auth()->guard('api')->user();
}
function create_coin_wallet_p2p($user_id)
{
    $items = getMissingCoinWallet_p2p($user_id);
    if (!empty($items)) {
        foreach ($items as $item) {
            storeNewWallet_p2p($item);
        }
    }
}

function storeNewWallet_p2p($item)
{
    $checkWallet = P2PWallet::where(['user_id' => $item['user_id'], 'coin_id' => $item['coin_id'], 'coin_type' => $item['coin_type']])->first();
    if (isset($checkWallet)) {
    } else {
        $checkWalletAgain = P2PWallet::where(['user_id' => $item['user_id'], 'coin_id' => $item['coin_id']])->first();
        if (empty($checkWalletAgain)) {
            $againCheck = P2PWallet::where(['user_id' => $item['user_id'], 'coin_id' => $item['coin_id']])->first();
            if (empty($againCheck)) {
                $a = P2PWallet::firstOrCreate([
                    'user_id' => $item['user_id'],
                    'coin_id' => $item['coin_id']
                ], [
                    'name' => $item['coin_type'] . ' wallet',
                    'coin_type' => $item['coin_type']
                ]);
            }
        }
    }
}

function getMissingCoinWallet_p2p($user_id)
{
    $coins = Coin::where(['status' => STATUS_ACTIVE])->get();
    $data = [];
    if (isset($coins[0])) {
        foreach ($coins as $coin) {
            $exist = P2PWallet::where(['user_id' => $user_id, 'coin_id' => $coin->id])->first();
            if (isset($exist)) {
            } else {
                $data[] = [
                    'coin_id' => $coin->id,
                    'coin_type' => $coin->coin_type,
                    'user_id' => $user_id,
                    'name' => $coin->coin_type . ' wallet',
                ];
            }
        }
    }
    return $data;
}

function statusOnOffAction_p2p($status)
{
    $color = 'btn-primary';
    $text = __('ON');
    if ($status == STATUS_REJECTED) {
        $color = 'btn-danger';
        $text = __('OFF');
        return "<button class=\"btn-sm $color \" disabled>$text</button>";
    }
    return "<button class=\"btn-sm $color \" disabled>$text</button>";
}

function ActionButtonForList_p2p($id, $eidt_url, $delete_url, $extra = [])
{
    try {
        $editRoute = isset($extra['id']) ? route($eidt_url, $extra['id']) : route($eidt_url);
        $deleteRoute = '';
        if (isset($extra['delete']) && !$extra['delete']) {
        } else {
            $deleteRoute = isset($extra['id']) ? route($delete_url, $extra['id']) : route($eidt_url);
        }

        $html = '<a title="' . __('Edit') . '" href="' . $editRoute . '" data-toggle="modal"><span class=""><i class="fa fa-pencil fa-lg" aria-hidden="true"></i>
        </span></a>';
        if (isset($extra['delete']) && !$extra['delete']) {
        } else {
            $html .= '<a title="' . __('Delete') . '" href="#delete_' . ($id) . '" data-toggle="modal"><span class=""><i class="text-danger fa fa-trash fa-lg" aria-hidden="true"></i>
            </span></a>';
            $html .= '<div id="delete_' . ($id) . '" class="modal fade delete" role="dialog">';
            $html .= '<div class="modal-dialog modal-sm">';
            $html .= '<div class="modal-content">';
            $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Delete') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
            $html .= '<form action="' . $deleteRoute . '"method="post">';
            $html .= '<input type="hidden" name="_token" value="' . csrf_token() . '" />';
            $html .= '<input type="hidden" name="id" value="' . $id . '" />';
            $html .= '<div class="modal-body">';
            $html .= '<p>' . __('Do you want to Delete?') . '</p>';
            $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
            $html .= '<button class="btn btn-danger" type="submit">' . __('Delete') . '</button>';
            $html .= '</div>';
            $html .= '</form>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        }
        return $html;
    } catch (\Exception $e) {
        storeException('ActionButtonForList_p2p', $e->getMessage());
        return 'N/A';
    }
}

function uploadFilep2p($new_file, $path, $old_file_name = null, $width = null, $height = null)
{
    try {
        $imgManager = new ImageManager(new Driver());
        if (!file_exists(public_path($path))) {
            mkdir(public_path($path), 0777, true);
        }
        if (isset($old_file_name) && $old_file_name != "" && file_exists($path . $old_file_name)) {
            unlink($path . $old_file_name);

        }
        if ($new_file == '')
            return false;
        $input['imagename'] = uniqid() . time() . '.' . $new_file->getClientOriginalExtension();
        $imgPath = public_path($path . $input['imagename']);

        $makeImg = $imgManager->read($new_file);
        if ($width != null && $height != null && is_int($width) && is_int($height)) {
            $makeImg->scale($width, $height);
        }
        $makeImg->toPng()->save($imgPath);
        return $input['imagename'];

    } catch (\Exception $e) {
        storeException('uploadFilep2p helper', $e->getMessage());
        return false;
    }

}

function find_payment_type_p2p($index = null)
{
    $array = [
        PAYMENT_METHOD_BANK => __('Bank Payment'),
        PAYMENT_METHOD_MOBILE => __('Mobile Acount Payment'),
        PAYMENT_METHOD_CARD => __('Card Payment'),
    ];
    if ($index && isset($array[$index]))
        return $array[$index];
    return $array;
}

// get user p2p wallet
function getUserP2pWallet($coinType, $userId)
{
    $coin = Coin::where('coin_type', $coinType)->first();
    $wallet = P2PWallet::firstOrCreate(['coin_id' => $coin->id, 'user_id' => $userId], [
        'name' => $coin->coin_type . ' wallet',
        'coin_type' => $coin->coin_type
    ]);
    return $wallet;
}
// get p2p order fees percentage
function getP2pOrderFees($amount, $percent)
{
    $fees = 0;
    if ($percent > 0 && $percent <= 100) {
        $fees = bcdiv(bcmul($percent, $amount, 8), 100, 8);
    }
    return $fees;
}

// make order transaction id
function makeOrderTransactionId($ads, $data)
{
    return uniqid() . $ads->id . $ads->currency . $data['seller_id'] . $ads->currency . $data['buyer_id'] . pMakeUniqueId();
}

// make unique id
function pMakeUniqueId()
{
    return uniqid() . date('') . time();
}

function uploadAnyFileP2P($new_file, $path, $old_file_name = null)
{
    if (!Storage::disk('public')->exists($path)) {
        Storage::disk('public')->makeDirectory($path);
    }

    if (Storage::disk('public')->exists($path . $old_file_name)) {
        Storage::disk('public')->delete($path . $old_file_name);
    }


    $fileName = uniqid() . time() . '.' . $new_file->getClientOriginalExtension();
    Storage::disk('public')->put($path . $fileName, file_get_contents($new_file));

    return $fileName;
}

function filePathP2P($storage_path, $imageName)
{
    $file_path = asset('storage/' . $storage_path) . '/' . $imageName;
    return $file_path;
}

function showUserImageP2P($id)
{
    $user = User::find($id);
    return imageSrcUser($user->photo, IMG_USER_VIEW_PATH);
}
// get conversasion data
function getChatDataP2P($data)
{
    $conversation = $data['conversation'];
    $temp = null;
    $temp['user_id'] = $conversation->sender_id;
    $temp['sender_image_link'] = isset($conversation->sender_id) ? showUserImageP2P($conversation->sender_id) : '';
    $temp['receiver_image_link'] = isset($conversation->receiver_id) ? showUserImageP2P($conversation->receiver_id) : '';
    $temp['message'] = $conversation->message;
    $temp['order_id'] = $conversation->order_id;
    $temp['user'] = $data['user'];
    $temp['user']['photo'] = showUserImageP2P($temp['user']['id']);
    $temp['file_path'] = $conversation->file ? filePathP2P(CONVERSATION_ATTACHMENT_PATH, $conversation->file) : '';
    return $temp;
}

function statusOnDisputeOrder_p2p($order_details)
{
    if (isset($order_details->dispute_details)) {
        if ($order_details->dispute_details->status == STATUS_INACTIVE) {
            return '<span class="btn-sm btn-warning">' . __('Pending') . '</span>';
        } elseif ($order_details->dispute_details->status == STATUS_ACTIVE) {
            return '<span class="btn-sm btn-success">' . __('Success') . '</span>';
        }
    } else {
        return '<span class="btn-sm btn-warning">' . __('N/A') . '</span>';
    }
}

function ActionButtonForDispute_p2p($query, $routeName)
{
    $html = '<a title="' . __('View Details') . '" href="' . route($routeName, [$query->uid]) . '" data-toggle="modal"><span class=""><i class="fa fa-eye fa-lg" aria-hidden="true"></i>
        </span></a>';

    return $html;
}

function tradeStatusListP2P($index = null)
{
    $array = [
        TRADE_STATUS_CANCELED_TIME_EXPIRED => __('Time Expired'),
        TRADE_STATUS_ESCROW => __('In Escrow'),
        TRADE_STATUS_PAYMENT_DONE => __('Payment Done'),
        TRADE_STATUS_TRANSFER_DONE => __('Transfer Done'),
        TRADE_STATUS_CANCELED => __('Canceled'),
        TRADE_STATUS_REFUNDED_BY_ADMIN => __('Refund By Admin'),
        TRADE_STATUS_RELEASED_BY_ADMIN => __('Released By Admin')
    ];
    if ($index && isset($array[$index]))
        return $array[$index];
    return $array;
}

function gitCardTradeStatusListP2P($index = null)
{
    $array = [
        TRADE_STATUS_CANCELED_TIME_EXPIRED => __('Time Expired'),
        TRADE_STATUS_ESCROW => __('In Escrow'),
        TRADE_STATUS_PAYMENT_DONE => __('Payment Done'),
        TRADE_STATUS_TRANSFER_DONE => __('Transfer Done'),
        TRADE_STATUS_CANCELED => __('Canceled'),
        TRADE_STATUS_REPORTED => __('Reported'),
        TRADE_STATUS_REFUNDED_BY_ADMIN => __('Refund By Admin'),
        TRADE_STATUS_RELEASED_BY_ADMIN => __('Released By Admin')
    ];
    if (isset($array[$index]))
        return $array[$index];
    return __("Not found");
}

function adminListP2P()
{
    $admin_list = User::where('role', 1)->where('status', STATUS_ACTIVE)->get();
    return $admin_list;
}

function ActionButtonForOrderP2P($query, $routeName)
{
    $html = '<a title="' . __('View Details') . '" href="' . route($routeName, [$query->uid]) . '" data-toggle="modal"><span class=""><i class="fa fa-eye fa-lg" aria-hidden="true"></i>
    </span></a>';

    return $html;
}

function tradeStatusRouteList($sub_menu)
{
    $route_list = [];
    foreach (tradeStatusListP2P() as $trade_status_key => $trade_status_value) {
        $temp = ['route' => 'getOrderList', 'title' => $trade_status_value, 'tab' => $sub_menu ?? '', 'tab_compare' => 'order_list_' . $trade_status_key, 'route_param' => $trade_status_key];
        array_push($route_list, $temp);
    }

    return $route_list;
}

// show p2p landing image
function p2pLandingImg($path)
{
    return settings($path) ? asset(P2P_LANDING_PATH . settings($path)) : '';
}

// get order time diff
function getOrderTimeDiff($order)
{
    $diffInMinutes = NULL;
    if ($order->status != TRADE_STATUS_CANCELED_TIME_EXPIRED && ($order->payment_time > 0)) {
        $givenTime = Carbon::parse($order->payment_expired_time);
        $currentTime = Carbon::now();
        $diffInMinutes = $givenTime->diffInSeconds($currentTime);
    }
    return $diffInMinutes > 0 ? $diffInMinutes : NULL;
}


// get user feedback
function getUserFeedback($user, $orders)
{
    $data['positive'] = 0;
    $data['negative'] = 0;
    $data['positive_feedback'] = 0;
    $feedBackOrder = $orders->where(function ($query) use ($user) {
        $query->where(['seller_id' => $user->id])->whereNotNull('buyer_feedback');
    })->orWhere(function ($query) use ($user) {
        $query->where(['buyer_id' => $user->id])->whereNotNull('seller_feedback');
    })->get();
    $myFeedBack = [];
    $positive_feedback_list = [];
    $negative_feedback_list = [];

    if (isset($feedBackOrder[0])) {
        foreach ($feedBackOrder as $feedback) {
            if ($user->id == $feedback->seller_id) {
                $myFeedBack[] = [
                    'feedback' => $feedback->buyer_feedback,
                    'feedback_type' => $feedback->buyer_feedback_type,
                    'user_name' => $feedback->buyer->nickname,
                    'user_img' => showUserImageP2P($feedback->buyer_id),
                ];
                if ($feedback->buyer_feedback_type == FEEDBACK_TYPE_POSITIVE) {
                    $positive_feedback_list[] = [
                        'feedback' => $feedback->buyer_feedback,
                        'feedback_type' => $feedback->buyer_feedback_type,
                        'user_name' => $feedback->buyer->nickname,
                        'user_img' => showUserImageP2P($feedback->buyer_id),
                    ];
                } else {
                    $negative_feedback_list[] = [
                        'feedback' => $feedback->buyer_feedback,
                        'feedback_type' => $feedback->buyer_feedback_type,
                        'user_name' => $feedback->buyer->nickname,
                        'user_img' => showUserImageP2P($feedback->buyer_id),
                    ];
                }
            } else {
                $myFeedBack[] = [
                    'feedback' => $feedback->seller_feedback,
                    'feedback_type' => $feedback->seller_feedback_type,
                    'user_name' => $feedback->seller->nickname,
                    'user_img' => showUserImageP2P($feedback->seller_id),
                ];
                if ($feedback->seller_feedback_type == FEEDBACK_TYPE_POSITIVE) {
                    $positive_feedback_list[] = [
                        'feedback' => $feedback->seller_feedback,
                        'feedback_type' => $feedback->seller_feedback_type,
                        'user_name' => $feedback->seller->nickname,
                        'user_img' => showUserImageP2P($feedback->seller_id),
                    ];
                } else {
                    $negative_feedback_list[] = [
                        'feedback' => $feedback->seller_feedback,
                        'feedback_type' => $feedback->seller_feedback_type,
                        'user_name' => $feedback->seller->nickname,
                        'user_img' => showUserImageP2P($feedback->seller_id),
                    ];
                }
            }

        }
    }

    $data['feedback_list'] = $myFeedBack;
    $data['positive_feedback_list'] = $positive_feedback_list;
    $data['negative_feedback_list'] = $negative_feedback_list;
    $data['positive'] = isset($positive_feedback_list[0]) ? count($positive_feedback_list) : 0;
    $data['negative'] = isset($negative_feedback_list[0]) ? count($negative_feedback_list) : 0;
    $data['total_feedback'] = bcadd($data['positive'], $data['negative'], 0);
    if ($data['total_feedback'] > 0) {
        $data['positive_feedback'] = bcdiv(bcmul($data['positive'], 100), $data['total_feedback'], 2);
    }

    return $data;
}

function userTradeInfoDetails($userId)
{
    $user = User::find($userId);
    $orders = POrder::where(fn($q) => $q->where('seller_id', $user->id)->orWhere('buyer_id', $user->id));
    return userTradeInfo($user, $orders);
}

// user tarde info
function userTradeInfo($user, $orders)
{
    $data['total_trade'] = 0;
    $data['completion_rate_30d'] = 0;
    $data['total_success_trade'] = 0;
    $data['total_trade'] = $orders->get()->count();
    $data['total_success_trade'] = $orders->where('is_success', STATUS_ACTIVE)->get()->count();
    $fistOrder = $orders->where('is_success', STATUS_ACTIVE)->first();
    $currentTime = Carbon::now();
    $data['first_order_at'] = 0;
    if (!empty($fistOrder)) {
        $firstOrderTime = Carbon::createFromFormat('Y-m-d H:i:s', $fistOrder->created_at);

        $data['first_order_at'] = $firstOrderTime->diffInDays($currentTime);
        if ($data['first_order_at'] == 0) {
            $data['first_order_at'] = 1;
        }
    }

    $registerTime = Carbon::createFromFormat('Y-m-d H:i:s', $user->created_at);
    $data['user_register_at'] = $registerTime->diffInDays($currentTime);

    $date = Carbon::now()->subDays(30);
    $orders30d = POrder::where(fn($q) => $q->where('seller_id', $user->id)->orWhere('buyer_id', $user->id))
        ->where('created_at', '>=', $date);
    if (isset($orders30d->get()[0])) {
        $totalIn30Day = $orders30d->count();
        $orders30dCmplete = $orders30d->where('is_success', STATUS_ACTIVE)->get()->count();
        $data['completion_rate_30d'] = bcdiv(bcmul($orders30dCmplete, 100), $totalIn30Day, 2);
    }

    return $data;
}

function showUserNickNameP2P($userId)
{
    $user = User::find($userId);
    return $user->nickname ?? __('Not Found');
}

function getGiftCardAdStatus($status)
{
    $array = [
        GIFT_CARD_INACTIVE => __("Inactive"),
        GIFT_CARD_ACTIVE => __("Active"),
        GIFT_CARD_SUCCESS => __("Success"),
        GIFT_CARD_CANCELED => __("Canceled"),
        GIFT_CARD_ONGOING => __("Ongoing"),
    ];
    if (isset($array[$status]))
        return $array[$status];
    return __("Not Found");
}
