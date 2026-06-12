<?php

namespace Modules\FutureTrade\Services\NotificationServices;

use App\Jobs\MailSend;
use App\Model\Notification;
use App\User;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Emum\MarginModeEnum;
use Modules\FutureTrade\Events\FutureTradeMessageBroadcastEvent;

class NotificationService
{
    public static function orderPlaced(int $user_id)
    {
        $response = __('Your order has been placed');
        self::sendMessage($user_id, $response);
    }

    public static function positionLiquidationWarning(int $user_id)
    {
        $response = __('Your Position Close to Liquidated');
        self::sendMessage($user_id, $response);

        $message = __('Your open position is approaching the liquidation threshold. Immediate action may be required to avoid automatic closure of your position.');
        self::notifyStoreAndBroadcast($user_id, $response, $message );
    }

    public static function positionLiquidated(
        int $user_id,
        ?string $coinPairCode = null,
        ?string $positionUid = null,
        ?MarginModeEnum $marginMode = null
    ) {
        $title = __('Position Liquidated');
        $message = __('Your open position has been liquidated.');

        $details = [];
        if ($coinPairCode) {
            $details[] = __('Pair: :pair', ['pair' => $coinPairCode]);
        }
        if ($positionUid) {
            $details[] = __('Position: :uid', ['uid' => $positionUid]);
        }
        if ($marginMode) {
            $details[] = __('Margin: :mode', ['mode' => $marginMode->label()]);
        }

        if (!empty($details)) {
            $message .= ' ' . implode(' ', $details);
        }

        self::sendMessage($user_id, $title);
        self::notifyStoreAndBroadcast($user_id, $title, $message);

        $user = User::find($user_id);
        if (!$user || empty($user->email)) {
            return;
        }

        $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        if ($name === '') {
            $name = $user->email;
        }

        $emailData = [
            'to' => $user->email,
            'name' => $name,
            'subject' => $title,
            'email_header' => $title,
            'email_message' => $message,
            'mailTemplate' => emailTemplateName('genericemail'),
        ];

        dispatch(new MailSend($emailData))->onQueue('send-mail');
    }

    public static function orderProcess(int $user_id)
    {
        $response = __('Order in process');
        self::sendMessage($user_id, $response);
    }
  
    public static function stopLimitOrderProcess(int $user_id)
    {
        $response = __('Stop-Limit order in process');
        self::sendMessage($user_id, $response);
    }

    public static function orderCancel(int $user_id, string|float $amount, string $coin, OrderType $orderType)
    {
        $response = __('Your :amount :coin :type order has been cancelled', [
            'amount' => $amount, 'coin' => $coin , 'type' => $orderType->label(true)
        ]);
        self::sendMessage($user_id, $response);
    }
    
    public static function positionClosed(int $user_id)
    {
        $response = __('Position Closed');
        self::sendMessage($user_id, $response);
    }
    
    public static function positionClosedOrder(int $user_id)
    {
        $response = __('Position Close Order Placed');
        self::sendMessage($user_id, $response);
    }

    public static function positionOpen(int $user_id)
    {
        $response = __('Position Opened');
        self::sendMessage($user_id, $response);
    }

    public static function orderFilled(int $user_id)
    {
        $response = __('Order Fully Filled');
        self::sendMessage($user_id, $response);
    }

    public static function sendMessage(int $user_id, string $message)
    {
        async(function() use($user_id, $message){
            rescue(
                fn() => FutureTradeMessageBroadcastEvent::dispatch(
                    $user_id,
                    $message
                )
            );
        });
    }

    public static function notifyStoreAndBroadcast($user_id,$title,$message)
    {
        try {
            $notification_details = Notification::create(['user_id' => $user_id, 'title' => $title, 'notification_body' => $message]);
            $channel_name = 'usernotification_'.$user_id;
            $event_name = 'receive_notification';
            $data['success'] = true;
            $data['user_id'] = $user_id;
            $data['message'] = $message;
            $data['notification_details'] = $notification_details;

            sendDataThroughWebSocket($channel_name,$event_name,$data);
        } catch (\Exception $e) {
            storeException('Future sendNotificationToUserUsingSocket',$e->getMessage());
        }
    }
}
