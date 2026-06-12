<?php
namespace Modules\KnowledgeBase\Http\Repositories;

use Modules\KnowledgeBase\Entities\SupportNotification;
class NotificationRepository
{

    public function createNotification($data)
    {
        try {
            $data['unique_code'] = uniqid() . date('') . time();
            $check_ticket = SupportNotification::where('ticket_user_id', $data['ticket_user_id'])->where('ticket_id', $data['ticket_id'])
                ->where('get_notification_by', $data['get_notification_by'])->first();
            if (isset($check_ticket)) {
                $check_ticket->type = $data['type'];
                $check_ticket->title = $data['title'];
                $check_ticket->status = $data['status'];
                $check_ticket->body = $data['body'];
                $check_ticket->save();
            } else {
                SupportNotification::create($data);
            }

            $response = ['success' => true, 'message' => __('Notification is created')];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("getCategoryList", $e->getMessage());
        }

        return $response;
    }
    public function getNotificationList()
    {
        $user = auth()->user();

        if (isset($user)) {
            if ($user->role == USER_ROLE_USER) {
                $notification_list = SupportNotification::where('ticket_user_id', $user->id)
                    ->where('get_notification_by', SUPPORT_NOTIFICATION_GET_BY_USER)->where('status', STATUS_ACTIVE)->get();

            } else {
                $notification_list = SupportNotification::where('get_notification_by', SUPPORT_NOTIFICATION_GET_BY_AGENT)
                    ->where('status', STATUS_ACTIVE)->get();
            }
            $response = ['success' => true, 'message' => __('Notification List'), 'data' => $notification_list];
        } else {
            $response = ['success' => false, 'message' => __('User not found')];
        }

        return $response;
    }

    public function getNotificationListToSendNotification($ticket_user_id)
    {
        $user = auth()->user();

        if (isset($user)) {
            if ($user->role == USER_ROLE_USER) {
                $notification_list = SupportNotification::where('get_notification_by', SUPPORT_NOTIFICATION_GET_BY_AGENT)
                    ->where('status', STATUS_ACTIVE)->get();

            } else {
                $notification_list = SupportNotification::where('ticket_user_id', $ticket_user_id)
                    ->where('get_notification_by', SUPPORT_NOTIFICATION_GET_BY_USER)->where('status', STATUS_ACTIVE)->get();

            }
            $response = ['success' => true, 'message' => __('Notification List'), 'data' => $notification_list];
        } else {
            $response = ['success' => false, 'message' => __('User not found')];
        }

        return $response;
    }

    public function getNotificationDetails($unique_code)
    {
        $notification_details = SupportNotification::where('unique_code', $unique_code)->first();
        if (isset($notification_details)) {
            $response = ['success' => true, 'message' => __('Notification details'), 'data' => $notification_details];
        } else {
            $response = ['success' => false, 'message' => __('Invalid Request')];
        }

        return $response;
    }

    public function deactiveNotificationByUniqueCode($unique_code)
    {
        $notification_details = SupportNotification::where('unique_code', $unique_code)->first();
        $notification_details->status = STATUS_INACTIVE;
        $notification_details->save();
        $response = ['success' => true, 'message' => __('Notification is detactived successfully')];
        return $response;
    }

    public function deactiveUserNotification($notification_type)
    {
        $user = auth()->user();
        $notification_list = SupportNotification::where('ticket_user_id', $user->id)->update('status', STATUS_INACTIVE);
        $response = ['success' => true, 'message' => __('User notification is deactivated')];
        return $response;

    }

    public function deactiveUserNotificationByTicketID($ticket_id)
    {
        $user = auth()->user();
        $notification = SupportNotification::where('ticket_user_id', $user->id)->where('ticket_id', $ticket_id)->first();
        if (isset($notification)) {
            $notification->update(['status', STATUS_INACTIVE]);
            $response = ['success' => true, 'message' => __('Notification deactive for ticket')];
        } else {
            $response = ['success' => false, 'message' => __('Notification is not found')];
        }

        return $response;
    }
}