<?php
namespace Modules\KnowledgeBase\Http\Services\Support;

use Modules\KnowledgeBase\Http\Repositories\Support\TicketRepository;

class TicketService {
    private $ticketRepository;
    public function __construct()
    {
        $this->ticketRepository = new TicketRepository;
    }

    public function getUserTicketList()
    {
        $response = $this->ticketRepository->getUserTicketList();
        return $response;
    }

    public function getUserTicketListByPaginate($limit, $offset)
    {
        $response = $this->ticketRepository->getUserTicketListByPaginate($limit, $offset);
        return $response;
    }
    public function getUserTicketListByPaginateWithSearch($limit, $offset, $request)
    {
        $response = $this->ticketRepository->getUserTicketListByPaginateWithSearch($limit, $offset, $request);
        return $response;
    }

    public function getUserTicketCountDetails()
    {
        $response = $this->ticketRepository->getUserTicketCountDetails();
        return $response;
    }
    public function storeTicket($request)
    {
        if ($request->hasFile('files')) 
        {
            foreach($request->file('files') as $file){
                $extension = $file->getClientOriginalExtension();
                if(!in_array($extension, getAllAllowedFileSupport()))
                {
                    
                    $allow_extension = implode(',',getAllAllowedFileSupport());
                    $response = ['success' => false, 'message' => __('Only allow file type are ').$allow_extension];
                    return $response;
                }

                $size = $file->getSize();
                if($size > 2000000)
                {
                    $response = ['success' => false, 'message' => __('File size is too large, maximum limit for per file is 2MB!')];
                    return $response;
                }
            }
        }

        $response = $this->ticketRepository->storeTicket($request);
        return $response;
    }

    public function ticketConversation($unique_code)
    {
        $response = $this->ticketRepository->ticketConversation($unique_code);
        return $response;
    }

    public function sendTicketConversation($request)
    {
        if ($request->hasFile('files_name')) 
        {
            foreach($request->file('files_name') as $file){
                $extension = $file->getClientOriginalExtension();
                if(!in_array($extension, getAllAllowedFileSupport()))
                {
                    $allow_extension = implode(',',getAllAllowedFileSupport());
                    $response = ['success' => false, 'message' => __('Only allow file type are ').$allow_extension];
                    return $response;
                }

                $size = $file->getSize();
                
                if($size > 2000000)
                {
                    $response = ['success' => false, 'message' => __('File size is too large, maximum limit for per file is 2MB!')];
                    return $response;
                }
            }
        }
        
        $response = $this->ticketRepository->sendTicketConversation($request);
        return $response;
    }

    public function getTicketListForAgent($request)
    {
        $response = $this->ticketRepository->getTicketListForAgent($request);
        return $response;
    }

    public function ticketStatusChange($request)
    {
        $response = $this->ticketRepository->ticketStatusChange($request);
        return $response;
    }

    public function ticketCategoryChange($request)
    {
        $response = $this->ticketRepository->ticketCategoryChange($request);
        return $response;
    }

    public function ticketProjectChange($request)
    {
        $response = $this->ticketRepository->ticketProjectChange($request);
        return $response;
    }

    public function getActiveAgentList()
    {
        $response = $this->ticketRepository->getActiveAgentList();
        return $response;
    }
    public function ticketAgentChange($request)
    {
        $response = $this->ticketRepository->ticketAgentChange($request);
        return $response;
    }

    public function getTicketDetailsByID($id)
    {
        $response = $this->ticketRepository->getTicketDetailsByID($id);
        return $response;
    }

    
}