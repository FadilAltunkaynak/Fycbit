<?php

namespace Modules\KnowledgeBase\Http\Controllers\Api;

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
    public function notificationDetails(Request $request)
    {
        if(!isset($request->unique_code))
        {
            $response = ['success' => false, 'message' => __('Unique Code is required!')];
            return response()->json($response);
        }
        $response = $this->notificationService->getNotificationDetails($request->unique_code);
        
        if($response['success'])
        {
            $notification_details = $response['data'];
            
            $this->notificationService->deactiveNotificationByUniqueCode($notification_details->unique_code);
            
            
        }
        return response()->json($response);
    }
}
