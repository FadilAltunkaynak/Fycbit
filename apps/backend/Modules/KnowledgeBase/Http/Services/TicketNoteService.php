<?php
namespace Modules\KnowledgeBase\Http\Services;

use Modules\KnowledgeBase\Http\Repositories\TicketNoteRepository;
use Modules\KnowledgeBase\Http\Repositories\Support\TicketRepository;
class TicketNoteService{

    private $ticketNoteService;
    private $ticketRepository;
    public function __construct()
    {
        $this->ticketNoteService = new TicketNoteRepository;
        $this->ticketRepository = new TicketRepository;
    }

    public function getTicketNoteList($ticket_id)
    {
        $response = $this->ticketNoteService->getTicketNoteList($ticket_id);
        return $response;
    }
    public function saveTicketNote($request)
    {
        try{
            $user = auth()->user();
            $ticket_response = $this->ticketRepository->getTicketDetailsByID($request->ticket_id);
            if($ticket_response['success'])
            {
                if(isset($request->notes))
                {
                    $data = [
                        'ticket_id'=> $request->ticket_id,
                        'user_id'=>$user->id,
                        'user_type'=>$user->role,
                        'notes'=>strip_tags($request->notes)
                    ];
        
                    $unique_code = isset($request->unique_code) ? $request->unique_code : null;
                    
                    $response = $this->ticketNoteService->saveTicketNote($unique_code, $data);
                }else{
                    $response = ['success'=>false, 'message'=>__('Note text field is required!')];
                }
            }else{
                $response = ['success'=>false, 'message'=>__('Invalid Request')];
            }
            
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("saveArticle", $e->getMessage());
        }
        return $response;
    }

    public function deleteTicketNote($unique_code)
    {
        $response = $this->ticketNoteService->deleteTicketNote($unique_code);
        return $response;
    }
}