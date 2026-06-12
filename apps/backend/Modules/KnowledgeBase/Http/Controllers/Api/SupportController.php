<?php

namespace Modules\KnowledgeBase\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\KnowledgeBase\Http\Requests\SupportChatRequest;
use Modules\KnowledgeBase\Http\Services\Support\TicketService;
use Modules\KnowledgeBase\Http\Services\Support\ProjectService;
use Modules\KnowledgeBase\Http\Requests\TicketRequest;
use Modules\KnowledgeBase\Http\Services\TicketNoteService;

class SupportController extends Controller
{
    private $ticketService;
    private $projectService;
    private $ticketNoteService;
    public function __construct()
    {
        $this->ticketService = new TicketService;
        $this->projectService = new ProjectService;
        $this->ticketNoteService = new TicketNoteService;
    }

    public function supportTicketList(Request $request)
    {
        if(isset($request->to_date) && !isset($request->from_date))
        {
            $response = ['success'=>true, 'message'=>__('From date is required')];
            return response()->json($response);
        }
        $limit = isset($request->limit)? $request->limit :25;
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
        $response = ['success'=>true, 'message'=>__('Ticket list with serch by pagination'), 'data'=>$data];
        return response()->json($response);
    }

    public function supportProjectList()
    {
        $response = $this->projectService->getActiveProjectList();
        return response()->json($response);
    }

    public function supportTicketStore(TicketRequest $request)
    {
        $response = $this->ticketService->storeTicket($request);
        return response()->json($response);
    }

    public function ticketConversationDetails(Request $request)
    {
        if(isset($request->unique_code))
        {
            $ticket_response = $this->ticketService->ticketConversation($request->unique_code);
            if($ticket_response['success'])
            {
                $ticket_details = $ticket_response['data']['ticket_details'];
                $data['ticket_details'] = $ticket_details;
                $data['conversation_list'] = $ticket_response['data']['conversation_list'];

                $note_response = $this->ticketNoteService->getTicketNoteList($ticket_details->id);
                if($note_response['success'])
                {
                    $data['ticket_note_list'] = $note_response['data'];
                }
                $response = ['success' => true, 'message' => __('Ticket details with conversation and ticket note list'), 'data' => $data];
            }else{
                $response = $ticket_response;
            }
            
        }else{
            $response = ['success' => false, 'message' => __('Ticket unique_code is required')];
        }
        return response()->json($response);
    }
    public function ticketConversationSend(SupportChatRequest $request)
    {
        $response = $this->ticketService->sendTicketConversation($request);
        return response()->json($response);
    }
}
