<?php
namespace Modules\KnowledgeBase\Http\Repositories;

use Modules\KnowledgeBase\Entities\SupportTicketNote;

class TicketNoteRepository{

    public function getTicketNoteList($ticket_id)
    {
        $user = auth()->user();
        if($user->role == USER_ROLE_USER)
        {
            $note_list = SupportTicketNote::where('ticket_id', $ticket_id)
                        ->where('user_id', $user->id)
                        ->where('user_type', $user->role)->get();
            $response = ['success' => true, 'message' => __('User ticket note'), 'data' => $note_list];

        }else{
            $note_list = SupportTicketNote::where('ticket_id', $ticket_id)
                                        ->where('user_type', $user->role)       
                                        ->get();
            $response = ['success' => true, 'message' => __('User ticket note'), 'data' => $note_list];
        }
        return $response;
    }
    public function saveTicketNote($unique_code, $data)
    {
        try{
            if($unique_code !=null)
            {
                $note = SupportTicketNote::where('unique_code', $unique_code)->first();
                $note->update($data);
                $response = ['success' => true, 'message' => __('Ticket Note is updated Successfully!')];
            }else{
                $data['unique_code'] = uniqid().date('').time();
                $note = SupportTicketNote::create($data);
                $response = ['success' => true, 'message' => __('Ticket Note is created Successfully!')];
            }
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("saveTicketNote", $e->getMessage());
        }
        return $response;
    }

    public function deleteTicketNote($unique_code)
    {
        $note = SupportTicketNote::where('unique_code', $unique_code)->first();
        if(isset($note))
        {
            $ticket_id = $note->ticket_id;
            $note->delete();
            $ticket_response = $this->getTicketNoteList($ticket_id);
            $ticket_list = $ticket_response['data'];

            $response = ['success' => true, 'message' => __('Ticket note is deleted'),'data'=>$ticket_list];
        }else{
            $response = ['success' => false, 'message' => __('Ticket note is not found')];
        }
        
        return $response;
    }
}