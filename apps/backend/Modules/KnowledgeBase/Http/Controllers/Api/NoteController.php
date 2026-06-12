<?php

namespace Modules\KnowledgeBase\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\KnowledgeBase\Http\Requests\NoteRequest;
use Modules\KnowledgeBase\Http\Services\TicketNoteService;
class NoteController extends Controller
{
    private $ticketNoteService;
    public function __construct()
    {
        $this->ticketNoteService = new TicketNoteService;
    }

    public function listTicketNote(Request $request)
    {
        $response = $this->ticketNoteService->getTicketNoteList($request->ticket_id);
        return response()->json($response);
    }

    public function saveTicketNote(NoteRequest $request)
    {
        $save_response = $this->ticketNoteService->saveTicketNote($request);
        if($save_response['success'])
        {
            $note_list_response = $this->ticketNoteService->getTicketNoteList($request->ticket_id);
            $data = $note_list_response['success']? $note_list_response['data']:null;
        }
        $response = ['success'=>$save_response['success'],'message'=>$save_response['message'],'data' => $data];

        return response()->json($response);
    }

    public function deleteTicketNote(Request $request)
    {
        if(isset($request->unique_code))
        {
            $response = $this->ticketNoteService->deleteTicketNote($request->unique_code);
            return response()->json($response);
        }else{
            $response = ['success' => true, 'message' => __('unique_code is required')];
            return response()->json($response);
        }
        
    }
}
