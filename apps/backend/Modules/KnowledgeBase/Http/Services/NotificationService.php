<?php
namespace Modules\KnowledgeBase\Http\Services;

use Modules\KnowledgeBase\Http\Repositories\NotificationRepository;
class NotificationService{

    private $notificationRepository;
    public function __construct()
    {
        $this->notificationRepository = new NotificationRepository;
    }

    public function getNotificationList()
    {
        $response = $this->notificationRepository->getNotificationList();
        return $response;
    }
    public function getNotificationDetails($unique_code)
    {
        $response = $this->notificationRepository->getNotificationDetails($unique_code);
        return $response;
    }

    public function deactiveNotificationByUniqueCode($unique_code)
    {
        $response = $this->notificationRepository->deactiveNotificationByUniqueCode($unique_code);
        return $response;
    }

    public function deactiveUserNotification($notification_type)
    {
        $response = $this->notificationRepository->deactiveUserNotification($notification_type);
        return $response;
    }

    public function deactiveUserNotificationByTicketID($ticket_id)
    {
        $response = $this->notificationRepository->deactiveUserNotificationByTicketID($ticket_id);
        return $response;
    }
}