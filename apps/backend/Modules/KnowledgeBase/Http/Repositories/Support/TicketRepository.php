<?php
namespace Modules\KnowledgeBase\Http\Repositories\Support;

use Amp\Success;
use App\User;
use Carbon\Carbon;
use Exception;
use Modules\KnowledgeBase\Entities\SupportTicket;
use Modules\KnowledgeBase\Entities\SupportConversation;
use Modules\KnowledgeBase\Entities\SupportConversationAttachment;
use Modules\KnowledgeBase\Entities\SupportCategory;
use Modules\KnowledgeBase\Entities\SupportProject;
use Modules\KnowledgeBase\Entities\SupportNotification;
use Modules\KnowledgeBase\Http\Repositories\NotificationRepository;

class TicketRepository {

    private $notificationRepository;
    public function __construct()
    {
        $this->notificationRepository = new NotificationRepository;
    }
    public function getUserTicketList()
    {
        $user = auth()->user();
        $ticket_list = SupportTicket::with('user')->where('user_id', $user->id)->latest()->get();
        $response = ['success' => true, 'message' => __('User Ticket List'), 'data' => $ticket_list];
        return $response;
    }
    public function getUserTicketListByPaginate($limit, $offset)
    {
        $user = auth()->user();
        $ticket_list = SupportTicket::with('user')->where('user_id', $user->id)->orderBy('is_seen_by_user','desc')->latest()->paginate($limit, ['*'], 'page', $offset);
        $response = ['success' => true, 'message' => __('User Ticket List'), 'data' => $ticket_list];
        return $response;
    }

    public function getUserTicketListByPaginateWithSearch($limit, $offset, $request)
    {
        $search = $request->search;
        $project = $request->project;
        $status = $request->status;
        $from_date = $request->from_date;
        $to_date = $request->to_date;

        $user = auth()->user();
        $ticket_list = SupportTicket::with(['user','agent','last_conversation'])->where('user_id', $user->id)
            ->when(isset($search), function ($query) use ($search) {
                $query->where('id', 'LIKE', "%{$search}%")
                    ->orWhere('title', 'LIKE', "%{$search}%")
                    ->orWhere('purchase_code', 'LIKE', "%{$search}%");
            })
            ->when(isset($request->status), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when(isset($request->project), function ($query) use ($request) {
                $query->where('project_id', $request->project);
            })
            ->when(isset($to_date), function ($query) use ($from_date, $to_date) {
                $query->wherebetween('last_conversation_time', [$from_date , $to_date]);
            })
            ->orderBy('is_seen_by_user','desc')->orderBy('last_conversation_time','desc')->paginate($limit, ['*'], 'page', $offset)
            ->appends(['search'=>$search,'project'=>$project,'status'=>$status,'from_date'=>$from_date,'to_date'=>$to_date]);

            $ticket_list->map(function($query){
                if(isset($query->agent))
                {
                    $query->agent_name = $query->agent->first_name. ' ' .$query->agent->last_name;
                }else{
                    $query->agent_name = null;
                }
            $query->project_name = isset($query->project) ? $query->project->name : null;
            $query->category_name = isset($query->category) ? $query->category->name : null;
            });
        $response = ['success' => true, 'message' => __('User Ticket List'), 'data' => $ticket_list];
        return $response;
    }

    public function storeTicket($request)
    {
        try{
            $user =  auth()->user();
            $ticket = new SupportTicket;
            $ticket->unique_code = uniqid().date('').time();
            $ticket->category_id = $request->category_id;
            $ticket->user_id = $user->id;
            $ticket->project_id = $request->project_id;
            $ticket->title = $request->title;
            $ticket->description = $request->description;
            $ticket->purchase_code = $request->purchase_code;
            $ticket->is_seen_by_user = SEEN;
            $ticket->status = TICKET_STATUS_PENDING;
            $ticket->last_conversation_time = Carbon::now();
            $ticket->save();

            $conversation = new SupportConversation;
            $conversation->ticket_id = $ticket->id;
            $conversation->message = $request->description;
            $conversation->conversation_type = CONVERSATION_TYPE_RAGULAR;
            $conversation->user_id = $user->id;


            if ($request->hasFile('files')) {
                $conversation->has_file = HAS_FILE_TRUE;
                $conversation->save();

                $files = $request->file('files');
                foreach($files as $file){
                    $extension = $file->getClientOriginalExtension();
                    $file_type = null;
                    if(in_array($extension, getAllowedImagesSupport()))
                    {
                        $file_type = 'img';
                    }else{
                        $file_type = 'file';
                    }
                    $imageName = uploadAnyFileSupport($file, FILE_KNOWLEDGE_BASE_SUPPORT_STORAGE_PATH);
                    $attachment = new SupportConversationAttachment;
                    $attachment->conversation_id = $conversation->id;
                    $attachment->file_link = asset('storage/'.FILE_KNOWLEDGE_BASE_SUPPORT_STORAGE_PATH).'/'.$imageName;
                    $attachment->file_type = $file_type;
                    $attachment->save();
                }
            }else{
                $conversation->has_file = HAS_FILE_FALSE;
                $conversation->save();
            }

            $notification_data['ticket_user_id'] = $ticket->user_id;
            $notification_data['ticket_id'] = $ticket->id;
            $notification_data['title'] = __("A new ticket is created and it's ticket id:") . $ticket->id;
            $notification_data['body'] = $ticket->description;
            $notification_data['type'] = SUPPORT_NOTIFICATION_TYPE_TICKET;
            $notification_data['get_notification_by'] = SUPPORT_NOTIFICATION_GET_BY_AGENT;
            $notification_data['status'] = STATUS_ACTIVE;

            $notification_response = $this->notificationRepository->createNotification($notification_data);

            if($notification_response['success'])
            {
                $channel_name = 'New-Ticket-Notification-Send-To-Agent';
                $event_name = 'Notification';
                $channel_data = formatNotificationData($ticket->user_id);
                storeException('chanel data', $channel_data);
                sendDataThroughWebSocket($channel_name, $event_name, $channel_data);

            }else{
                return $notification_response;
            }

            $response = ['success' => true, 'message' => __('Ticket is stored successfully')];
        }catch(Exception $e){
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("deleteProject", $e->getMessage());
        }
        return $response;
    }

    public function ticketDetails($unique_code)
    {
        $ticket_details = SupportTicket::with(['agent'])->where('unique_code', $unique_code)->first();
        if(isset($ticket_details))
        {
            $response = ['success' => true, 'message' => __('Ticket Details'), 'data' => $ticket_details];
        }else{
            $response = ['success' => false, 'message' => __('Invalid Request')];
        }

        return $response;
    }

    public function ticketConversation($unique_code)
    {
        try{
            $data = null;
            $user = auth()->user();

            $ticket_response = $this->ticketDetails($unique_code);

            if($ticket_response['success']) {

                $ticket_details = $ticket_response['data'];

                $ticket_details['project_name'] = isset($ticket_details->project) ? $ticket_details->project->name : null;

                if(isset($ticket_details['agent']))
                {
                    $ticket_details['agent']['photo'] = showUserImageSupport($ticket_details['agent']['id']);
                }

                $data['ticket_details'] = $ticket_details;

                if(isset($ticket_details)){
                    if($user->id == $ticket_details->user_id)
                    {
                        $conversation_list = SupportConversation::with(['user','conversationAttachment'])->where('ticket_id', $ticket_details->id)->where('conversation_type', CONVERSATION_TYPE_RAGULAR)->get();
                    }else{
                        $conversation_list = SupportConversation::with(['user','conversationAttachment'])->where('ticket_id', $ticket_details->id)->get();
                    }
                    $conversation_list->map(function ($query) {
                        if(isset($query->user))
                        {
                            $query->user->photo = showUserImageSupport($query->user->id);
                        }
                    });
                    $data['conversation_list'] = $conversation_list;
                }else{
                    $response = ['success' => false, 'message' => __('Ticket not found!')];
                }
                $response = ['success' => true, 'message' => __('Ticket details with conversations!'),'data' => $data];
            }else{
                $response = $ticket_response;
            }

        }catch(Exception $e){
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("ticketConversation", $e->getMessage());
        }
        return $response;
    }

    public function sendTicketConversation($request)
    {
        try{
            if(!$request->hasFile('files_name') && !isset($request->message))
            {
                $response = ['success' => false, 'message' => __('Write something to send!')];
                return $response;
            }
            $user =  auth()->user();

            $ticket_response = $this->ticketDetails($request->ticket_unique_code);
            if($ticket_response['success'])
            {
                $ticket_details = $ticket_response['data'];

                if($ticket_details->status == TICKET_STATUS_CLOSE_FOREAVER)
                {
                    $response = ['success' => false, 'message' => __('Ticket is colosed forever, you can not reply this ticket')];
                    return $response;
                }elseif($ticket_details->status != TICKET_STATUS_OPEN && $user->id != $ticket_details->user_id){

                    $message = __('Ticket status is changed from ').ticketStatus($ticket_details->status). __(' to '). ticketStatus(TICKET_STATUS_OPEN). __(' by '). $user->first_name.' '. $user->last_name;
                    $this->createStatusConversation($ticket_details->id, $message, $user->id);
                    $ticket_details->status = TICKET_STATUS_OPEN;
                }

                $conversation_response = $this->createRegularConversation($ticket_details, $request);
                if($conversation_response['success'] == false)
                {
                    return $conversation_response;
                }else{
                    $response = $conversation_response;
                }

                $ticket_details->last_conversation_time = Carbon::now();

                if($user->id != $ticket_details->user_id )
                {
                    $ticket_details->is_seen_by_user = UNSEEN;

                    if($ticket_details->assigned_agent_id == null)
                    {
                        $agent_id = $user->id;

                        $ticket_details->assigned_agent_id = $agent_id;

                        $message = __('Ticket is assign to ').$user->first_name . ' '. $user->last_name. __(' by his-self.');

                        $this->createStatusConversation($ticket_details->id, $message, $user->id);

                    }
                }else{
                    $ticket_details->is_seen_by_user = SEEN;

                }

                $ticket_details->save();

                //notification send start
                $notification_data['ticket_user_id'] = $ticket_details->user_id;
                $notification_data['ticket_id'] = $ticket_details->id;
                $notification_data['body'] = strip_tags($request->message);
                $notification_data['status'] = STATUS_ACTIVE;

                if($user->id != $ticket_details->user_id)
                {
                    $notification_data['title'] = __("A new message is sent by agent and it's ticket id:") . $ticket_details->id;
                    $notification_data['type'] = SUPPORT_NOTIFICATION_TYPE_TICKET_CONVERSATION;
                    $notification_data['get_notification_by'] = SUPPORT_NOTIFICATION_GET_BY_USER;

                    $notification_channel_name = 'New-Ticket-Notification-Send-To-User-'.$ticket_details->user_id;

                }else{
                    $notification_data['title'] = __("A new message is sent by user and it's ticket id:") . $ticket_details->id;
                    $notification_data['type'] = SUPPORT_NOTIFICATION_TYPE_TICKET_CONVERSATION;
                    $notification_data['get_notification_by'] = SUPPORT_NOTIFICATION_GET_BY_AGENT;

                    $notification_channel_name = 'New-Ticket-Notification-Send-To-Agent';

                }

                $notification_response = $this->notificationRepository->createNotification($notification_data);

                if($notification_response['success'])
                {
                    $notification_event_name = 'Notification';

                    $notification_channel_data = formatNotificationData($ticket_details->user_id);
                    // storeException('chanel data', $notification_channel_data);
                    sendDataThroughWebSocket($notification_channel_name, $notification_event_name, $notification_channel_data);

                }else{
                    return $notification_response;
                }

            }else{
                $response = ['success' => false, 'message' => __('Message is not sent successfully')];
            }

        }catch(Exception $e){
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("sendTicketConversation", $e->getMessage());
        }
        return $response;
    }
    public function createRegularConversation($ticket_details,$request)
    {
        try{
            $user =  auth()->user();
            $conversation = new SupportConversation;
            $conversation->ticket_id = $ticket_details->id;
            $conversation->message = strip_tags($request->message);
            $conversation->conversation_type = CONVERSATION_TYPE_RAGULAR;
            $conversation->user_id = $user->id;
            $tempAttachment = [];
            if ($request->hasFile('files_name')) {

                $conversation->has_file = HAS_FILE_TRUE;
                $conversation->save();
                $files = $request->file('files_name');
                foreach($request->file('files_name') as $file){
                    $extension = $file->getClientOriginalExtension();
                    $file_type = null;
                    if(in_array($extension, getAllowedImagesSupport()))
                    {
                        $file_type = 'img';
                    }else{
                        $file_type = 'file';
                    }
                    $imageName = uploadAnyFileSupport($file, FILE_KNOWLEDGE_BASE_SUPPORT_STORAGE_PATH);
                    $attachment = new SupportConversationAttachment;
                    $attachment->conversation_id = $conversation->id;
                    $attachment->file_link = asset('storage/'.FILE_KNOWLEDGE_BASE_SUPPORT_STORAGE_PATH).'/'.$imageName;
                    $attachment->file_type = $file_type;
                    $attachment->save();

                    array_push($tempAttachment,$attachment);
                }
            }else{
                $conversation->has_file = HAS_FILE_FALSE;
                $conversation->save();
            }

            $sender_id = $user->id;
            $receiver_id = ($user->id == $ticket_details->user_id) ? $ticket_details->assigned_agent_id : $ticket_details->user_id;
            $data['user'] = $user;
            $data['conversation'] = $conversation;
            $data['attachment'] = $tempAttachment;
            $data['conversation']['sender_id'] = $sender_id;
            $data['conversation']['receiver_id'] = $receiver_id;
            $response = ['success' => true, 'message' => __('Message is sent successfully'),'data'=>getChatDataSupport($data)];
            $channel_id = $sender_id . '-' . $ticket_details->unique_code;
            $channel_id2 = $receiver_id . '-' . $ticket_details->unique_code;
            $channel_name = 'New-Message-' . $channel_id;
            $channel_name2 = 'New-Message-' . $channel_id2;
            $event_name = 'Conversation';
            $channel_data = $response;
            // storeException('chanel data', $channel_data);
            sendDataThroughWebSocket($channel_name, $event_name, $channel_data);
            sendDataThroughWebSocket($channel_name2, $event_name, $channel_data);
        }catch(Exception $e){
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("createRegularConversation", $e->getMessage());
        }
        return $response;
    }

    public function createStatusConversation($ticket_id,$message, $user_id)
    {
        $status_conversation = new SupportConversation;
        $status_conversation->ticket_id = $ticket_id;
        $status_conversation->message = $message;
        $status_conversation->conversation_type = CONVERSATION_TYPE_STATUS;
        $status_conversation->has_file = HAS_FILE_FALSE;
        $status_conversation->user_id = $user_id;
        $status_conversation->save();

        $response = ['success' => true, 'message' => __('Create status successfully')];
        return $response;
    }

    public function getTicketListForAgent($request)
    {
        $ticket_list = SupportTicket::with(['user','agent','last_conversation'])->when(isset($request->assigned_type), function ($query) use ($request) {
            if ($request->assigned_type == TICKET_ASSIGN_STATUS_UNASSIGNED) {
                $query->whereNull('assigned_agent_id');
            } elseif ($request->assigned_type == TICKET_ASSIGN_STATUS_ASSIGNED) {
                $query->whereNotNull('assigned_agent_id');
            } elseif ($request->assigned_type == TICKET_ASSIGN_STATUS_SELF) {
                $query->where('assigned_agent_id', auth()->user()->id);
            } else {
                $query;
            }
        })
        ->when(isset($request->is_seen), function($query) use ($request) {

            $query->where('is_seen_by_user', $request->is_seen);

        })
        ->when(isset($request->category_type), function ($query) use ($request) {
            $query->where('category_id', $request->category_type);
        })
        ->when(isset($request->project_type), function ($query) use ($request) {
            $query->where('project_id', $request->project_type);
        })
        ->when(isset($request->status_type), function ($query) use ($request) {
            $query->where('status', $request->status_type);
        })
        ->when(isset($request->search), function($query) use($request){
            $query->where('id', 'LIKE', "%{$request->search}%")
                    ->orWhere('title','LIKE', "%{$request->search}%")
                    ->orWhere('purchase_code','LIKE', "%{$request->search}%");
        })
        ->orderBy('is_seen_by_user','asc')->orderBy('last_conversation_time','desc')->get();

        if($ticket_list->count() == 0 ) {

            $user_ids = User::where('first_name', 'LIKE', "%{$request->search}%")
                ->orWhere('last_name', 'LIKE', "%{$request->search}%")
                ->orWhere('email', 'LIKE', "%{$request->search}%")->pluck('id')->toArray();

            $ticket_list = SupportTicket::with(['user','agent','last_conversation'])->whereIn('user_id', $user_ids)->orderBy('last_conversation_time','desc')
                                            ->orderBy('is_seen_by_user','asc')->get();
        }

        $response = ['success'=>true, 'message'=>__('Ticket list for agent'), 'data'=>$ticket_list];
        return $response;
    }

    public function ticketStatusChange($request)
    {
        try{
            $user = auth()->user();
            $ticket = SupportTicket::where('unique_code', $request->ticket_unique_code)->first();

            if(isset($ticket))
            {
                $message = __('Ticket status is changed from ').ticketStatus($ticket->status). __(' to '). ticketStatus($request->ticket_status). __(' by '). $user->first_name.' '. $user->last_name;
                $this->createStatusConversation($ticket->id, $message, $user->id);

                $ticket->status = $request->ticket_status;
                $ticket->save();

                $response = ['success' => true, 'message' => __('Ticket status is changed')];
            }else{
                $response = ['success' => false, 'message' => __('Ticket is not found')];
            }
        }catch(Exception $e){
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("ticketConversation", $e->getMessage());
        }
        return $response;
    }

    public function ticketCategoryChange($request)
    {
        try{
            $user = auth()->user();
            $ticket = SupportTicket::where('unique_code', $request->ticket_unique_code)->first();

            if(isset($ticket))
            {
                $previous_category_name = isset($ticket->category) ? $ticket->category->name : __('Null');
                $category = SupportCategory::find($request->category_id);
                if(isset($category))
                {
                    $present_category_name = $category->name;
                }else{
                    $present_category_name = __('Null');
                }

                $message = __('Ticket category is changed from ') .$previous_category_name .__(' to '). $present_category_name.__(' by '). $user->first_name.' '. $user->last_name;

                $this->createStatusConversation($ticket->id, $message, $user->id);

                $ticket->category_id = $request->category_id;
                $ticket->save();

                $response = ['success' => true, 'message' => __('Ticket category is changed')];
            }else{
                $response = ['success' => false, 'message' => __('Ticket is not found')];
            }
        }catch(Exception $e){
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("ticketConversation", $e->getMessage());
        }
        return $response;
    }

    public function ticketProjectChange($request)
    {
        try{
            $user = auth()->user();
            $ticket = SupportTicket::where('unique_code', $request->ticket_unique_code)->first();
            if(isset($ticket))
            {
                $previous_project_name = isset($ticket->project) ? $ticket->project->name : __('Null');
                $project = SupportProject::find($request->project_id);
                if(isset($project))
                {
                    $present_project_name = $project->name;
                }else{
                    $present_project_name = __('Null');
                }

                $message = __('Ticket project is changed from ') .$previous_project_name .__(' to '). $present_project_name.__(' by '). $user->first_name.' '. $user->last_name;

                $this->createStatusConversation($ticket->id, $message, $user->id);

                $ticket->project_id = $request->project_id;
                $ticket->save();

                $response = ['success' => true, 'message' => __('Ticket project is changed')];
            }else{
                $response = ['success' => false, 'message' => __('Ticket is not found')];
            }

        }catch(Exception $e){
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("ticketConversation", $e->getMessage());
        }
        return $response;
    }

    public function ticketAgentChange($request)
    {
        try{
            $user = auth()->user();
            $ticket = SupportTicket::where('unique_code', $request->ticket_unique_code)->first();

            if(isset($ticket))
            {
                $agent = User::find($request->agent_id);
                $previous_agent_name = isset($ticket->agent) ? $ticket->agent->first_name.' '.$ticket->agent->last_name : __('Null');
                $present_agent_name = isset($agent) ? $agent->first_name.' '.$agent->last_name: __('Null');
                $message = __('Ticket agent is changed from ') .$previous_agent_name .__(' to '). $present_agent_name.__(' by '). $user->first_name.' '. $user->last_name;

                $this->createStatusConversation($ticket->id, $message, $user->id);

                $ticket->assigned_agent_id = $request->agent_id;
                $ticket->save();

                $response = ['success' => true, 'message' => __('Ticket agent is changed')];
            }else{
                $response = ['success' => false, 'message' => __('Ticket is not found')];
            }
        }catch(Exception $e){
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("ticketConversation", $e->getMessage());
        }
        return $response;
    }

    public function getActiveAgentList()
    {
        $agent_list = User::where('role', USER_ROLE_ADMIN)->where('status', STATUS_ACTIVE)->get();
        $response = ['success' => true, 'message' => __('Active Agent List'), 'data' => $agent_list];
        return $response;
    }

    public function getTicketDetailsByID($id)
    {
        $ticket_details = SupportTicket::find($id);
        $response = ['success'=>true, 'message'=>__('Ticket details by id'), 'data'=>$ticket_details];
        return $response;
    }

    public function ticketSearch($request)
    {
        $ticket_list = SupportTicket::where('id', 'LIKE', "%{$request->search}%")
                                        ->orWhere('title','LIKE', "%{$request->search}%")
                                        ->orWhere('purchase_code','LIKE', "%{$request->search}%");
    }

    public function getUserTicketCountDetails()
    {
        $user = auth()->user();
        if(isset($user) && $user->role == USER_ROLE_USER)
        {
            $ticket_list = SupportTicket::where('user_id', $user->id)->get();

        }else{
            $ticket_list = SupportTicket::get();
        }

        $data['total_ticket_count'] = $ticket_list->count();
        $data['total_pending_ticket_count'] = $ticket_list->where('status',TICKET_STATUS_PENDING)->count();
        $data['total_open_ticket_count'] = $ticket_list->where('status',TICKET_STATUS_OPEN)->count();
        $data['total_close_ticket_count'] = $ticket_list->where('status',TICKET_STATUS_CLOSE)->count();
        $data['total_close_forever_ticket_count'] = $ticket_list->where('status', TICKET_STATUS_CLOSE_FOREAVER)->count();
        $response = ['success' => true, 'message' => __('Ticket Count Details'), 'data' => $data];

        return $response;
    }
}
