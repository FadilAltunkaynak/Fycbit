<?php

namespace Modules\KnowledgeBase\Http\Controllers\Admin\Support;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\KnowledgeBase\Http\Services\TicketNoteService;

class NoteController extends Controller
{
    private $ticketNoteService;
    public function __construct()
    {
        $this->ticketNoteService = new TicketNoteService;
    }

    public function saveTicketNote(Request $request)
    {
        $response = $this->ticketNoteService->saveTicketNote($request);
        if($response['success'])
        {
            return back()->with(['success' => $response['message']]);
        }else{
            return back()->with(['dismiss' => $response['message']]);
        }
    }

    public function deleteTicketNote($unique_code)
    {
        $response = $this->ticketNoteService->deleteTicketNote($unique_code);
        if($response['success'])
        {
            return back()->with(['success' => $response['message']]);
        }else{
            return back()->with(['dismiss' => $response['message']]);
        }
    }
}
