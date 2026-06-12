<?php

namespace Modules\KnowledgeBase\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\KnowledgeBase\Http\Services\NotificationService;
use Modules\KnowledgeBase\Http\Services\Support\TicketService;

class NotificationController extends Controller
{
    private $notificationService;
    private $ticketService;
    public function __construct()
    {
        $this->notificationService = new NotificationService;
        $this->ticketService = new TicketService;
    }
    public function notificationDetails($unique_code)
    {
        $response = $this->notificationService->getNotificationDetails($unique_code);
        if($response['success'])
        {
            $notification_details = $response['data'];
            $this->notificationService->deactiveNotificationByUniqueCode($notification_details->unique_code);
            

            if($notification_details->type == SUPPORT_NOTIFICATION_TYPE_TICKET)
            {
                return redirect()->route('support_dashboard');
            }elseif($notification_details->type == SUPPORT_NOTIFICATION_TYPE_TICKET_CONVERSATION)
            {
                $ticket_response = $this->ticketService->getTicketDetailsByID($notification_details->ticket_id);
                if($ticket_response['success'])
                {
                    $ticket_details = $ticket_response['data'];
                    return redirect()->route('support_ticket_conversation_details', $ticket_details->unique_code);
                }else{
                    return back()->with(['dismiss' => __('Ticket details not found')]);
                }
            }
            return back()->with(['dismiss' => __('Something went wrong')]);

        }else{
            return back()->with(['dismiss' =>$response['message']]);
        }

    }
}
