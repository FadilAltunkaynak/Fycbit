<?php

namespace Modules\KnowledgeBase\Http\Controllers\User;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\KnowledgeBase\Http\Services\NotificationService;
use Modules\KnowledgeBase\Http\Services\Support\TicketService;
use Modules\KnowledgeBase\Http\Services\Support\ProjectService;
use Modules\KnowledgeBase\Http\Requests\TicketRequest;
use Modules\KnowledgeBase\Http\Services\TicketNoteService;

class SupportController extends Controller
{
    private $ticketService;
    private $projectService;
    private $ticketNoteService;
    private $notificationService;
    public function __construct()
    {
        $this->ticketService = new TicketService;
        $this->projectService = new ProjectService;
        $this->notificationService = new NotificationService;
        $this->ticketNoteService = new TicketNoteService;
    }
    public function index(Request $request)
    {
        if(isset($request->to_date) && !isset($request->from_date))
        {
            return back()->with(['dismiss' => __('From date is required')]);
        }
        $limit = 25;
        $offset = isset($request->page)? $request->page : 1;

        $data = null;
        $data['search'] = $request->search;
        $data['status'] = $request->status;
        $data['project'] = $request->project;
        $data['from_date'] = $request->from_date;
        $data['to_date'] = $request->to_date;

        $ticket_response = $this->ticketService->getUserTicketListByPaginateWithSearch($limit, $offset, $request);
        if($ticket_response['success'])
        {
            $ticket_list = $ticket_response['data'];
            $data['ticket_list'] = $ticket_list;
            $ticket_count_response = $this->ticketService->getUserTicketCountDetails();
            if($ticket_count_response['success'])
            {
                $data['ticket_count'] = $ticket_count_response['data'];
            }
        }

        return view('knowledgebase::user.support.dashboard',$data);
    }

    public function createTicket()
    {
        $data = null;
        $project_response = $this->projectService->getActiveProjectList();
        if($project_response['success'])
        {
            $data['project_list'] = $project_response['data'];
        }

        return view('knowledgebase::user.support.create-ticket',$data);
    }
    public function storeTicket(TicketRequest $request)
    {
        $response = $this->ticketService->storeTicket($request);

        if($response['success'])
        {
            return redirect()->route('support_dashboard')->with(['success' => $response['message']]);
        }else{
            return back()->with(['dismiss' => $response['message']]);
        }
    }

    public function ticketConversationDetails($unique_code)
    {
        $response = $this->ticketService->ticketConversation($unique_code);
        if($response['success'])
        {
            $ticket_details = $response['data']['ticket_details'];

            $note_response = $this->ticketNoteService->getTicketNoteList($ticket_details->id);
            if($note_response['success'])
            {
                $data['ticket_note_list'] = $note_response['data'];
            }

            $this->notificationService->deactiveUserNotificationByTicketID($ticket_details->id);

            return view('knowledgebase::user.support.conversation-details', $response['data'],$data);
        }else{
            return back()->with(['dismiss' => $response['message']]);
        }
    }

    public function ticketConversationSend(Request $request)
    {
        $response = $this->ticketService->sendTicketConversation($request);
        return response()->json($response);
    }

}
