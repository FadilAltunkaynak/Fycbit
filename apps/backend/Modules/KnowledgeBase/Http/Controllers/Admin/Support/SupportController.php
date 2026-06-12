<?php

namespace Modules\KnowledgeBase\Http\Controllers\Admin\Support;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\KnowledgeBase\Http\Services\Support\TicketService;
use Modules\KnowledgeBase\Http\Services\Support\CategoryService;
use Modules\KnowledgeBase\Http\Services\Support\ProjectService;
use Modules\KnowledgeBase\Http\Services\TicketNoteService;

class SupportController extends Controller
{
    protected $categoryService;
    protected $ticketService;
    protected $projectService;
    protected $ticketNoteService;
    public function __construct()
    {
        $this->categoryService = new CategoryService;
        $this->ticketService = new TicketService;
        $this->projectService = new ProjectService;
        $this->ticketNoteService = new TicketNoteService;
    }
    public function list(Request $request)
    {
        $data['title'] = __('Ticket List');
        if(isset($request->assigned_type)){
            $data['sub_menu'] = $request->assigned_type;
        }elseif(isset($request->is_seen)){
            $data['sub_menu'] = $request->is_seen == SEEN ? 'unseen-by-agent':'seen-by-agent';
        }else{
            $data['sub_menu'] = 'support-all-ticket';
        }

        $category_response = $this->categoryService->getActiveCategoryList();
        if($category_response['success'])
        {
            $data['category_list'] = $category_response['data'];
        }

        $project_response = $this->projectService->getProjectList();
        if($project_response['success'])
        {
            $data['project_list'] = $project_response['data'];
        }

        $response = $this->ticketService->getTicketListForAgent($request);
        if($response['success'])
        {
            $data['ticket_list'] = $response['data'];
        }

        return view('knowledgebase::admin.ticket.list', $data);
    }

    public function ticket_conversation($unique_code)
    {
        $data['title'] = __('Conversation Details');

        $response = $this->ticketService->ticketConversation($unique_code);
        if($response['success'])
        {
            $data['data'] = $response['data'];
            $ticket_details = $response['data']['ticket_details'];

            $ticket_note_response = $this->ticketNoteService->getTicketNoteList($ticket_details->id);
            if($ticket_note_response['success'])
            {
                $data['ticket_note_list'] = $ticket_note_response['data'];
            }
        }

        $category_response = $this->categoryService->getActiveCategoryList();
        if($category_response['success'])
        {
            $data['category_list'] = $category_response['data'];
        }
        $project_response = $this->projectService->getActiveProjectList();

        if($project_response['success'])
        {
            $data['project_list'] = $project_response['data'];
        }

        $agent_response = $this->ticketService->getActiveAgentList();
        if($agent_response['success'])
        {
            $data['agent_list'] = $agent_response['data'];
        }


        return view('knowledgebase::admin.ticket.conversation-details', $data);
    }

    public function ticketConversationSend(Request $request)
    {
        $response = $this->ticketService->sendTicketConversation($request);
        return response()->json($response);
    }

    public function ticketStatusChange(Request $request)
    {
        $response = $this->ticketService->ticketStatusChange($request);

        return response()->json($response);
    }

    public function ticketCategoryChange(Request $request)
    {
        $response = $this->ticketService->ticketCategoryChange($request);
        return response()->json($response);
    }
    public function ticketProjectChange(Request $request)
    {
        $response = $this->ticketService->ticketProjectChange($request);
        return response()->json($response);
    }

    public function ticketAgentChange(Request $request)
    {
        $response = $this->ticketService->ticketAgentChange($request);
        return response()->json($response);
    }
}
