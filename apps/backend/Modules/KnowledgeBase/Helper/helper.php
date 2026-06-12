<?php

use App\Model\SocialMedia;
use Illuminate\Support\Str;
use App\User;
use Modules\KnowledgeBase\Entities\KnowledgebaseSupportSetting;
use Modules\KnowledgeBase\Http\Repositories\NotificationRepository;
use Illuminate\Support\Facades\Storage;
use Modules\KnowledgeBase\Entities\KnowledgebaseFontawesome;
use Modules\KnowledgeBase\Entities\SupportProject;

const CONVERSATION_TYPE_RAGULAR = 1;
const CONVERSATION_TYPE_STATUS = 2;

const HAS_FILE_TRUE = 1;
const HAS_FILE_FALSE = 0;

const SEEN = 1;
const UNSEEN = 2;

const KNOWLEDGEBASE_URL_TYPE_FRONTEND = 1;
const KNOWLEDGEBASE_URL_TYPE_BACKEND = 2;

const FILE_KNOWLEDGE_BASE_STORAGE_PATH = 'knowledgebase/';
const FILE_KNOWLEDGE_BASE_VIEW_PATH = 'storage/knowledgebase/';
const FILE_KNOWLEDGE_BASE_SUPPORT_STORAGE_PATH = 'knowledgebase/support/';
const FILE_KNOWLEDGE_BASE_SUPPORT_VIEW_PATH = 'storage/knowledgebase/support/';

const TICKET_STATUS_PENDING = 1;
const TICKET_STATUS_OPEN = 2;
const TICKET_STATUS_CLOSE = 3;
const TICKET_STATUS_CLOSE_FOREAVER = 4;

const TICKET_ASSIGN_STATUS_UNASSIGNED = 1;
const TICKET_ASSIGN_STATUS_ASSIGNED = 2;
const TICKET_ASSIGN_STATUS_SELF = 3;

const SUPPORT_NOTIFICATION_TYPE_TICKET = 1;
const SUPPORT_NOTIFICATION_TYPE_TICKET_CONVERSATION = 2;

const SUPPORT_NOTIFICATION_GET_BY_USER = 1;
const SUPPORT_NOTIFICATION_GET_BY_AGENT = 2;

function ticketStatus($input = null)
{
    $output = [
        TICKET_STATUS_PENDING => __('Pending'),
        TICKET_STATUS_OPEN => __('Open'),
        TICKET_STATUS_CLOSE => __('Close'),
        TICKET_STATUS_CLOSE_FOREAVER => __('Close Forever')
   ];

   if (is_null($input)) {
       return $output;
   } else {
       return $output[$input];
   }
}
function ticketStatusAdmin($input = null)
{
    $output = [
        TICKET_STATUS_PENDING => '<span class="badge bg-primary">'.__('Pending').'</span>',
        TICKET_STATUS_OPEN => '<span class="badge bg-success">'.__('Open').'</span>',
        TICKET_STATUS_CLOSE => '<span class="badge bg-warning">'.__('Close').'</span>',
        TICKET_STATUS_CLOSE_FOREAVER => '<span class="badge bg-danger">'.__('Close Forever').'</span>',
   ];

   if (is_null($input)) {
       return $output;
   } else {
       return $output[$input];
   }
}


function showUserImageSupport($id)
{
    $user = User::find($id);
    return imageSrcUser($user->photo, IMG_USER_VIEW_PATH);
}
function getAllowedImagesSupport()
{
    return ['png', 'jpg', 'jpeg', 'gif'];
}
function getAllowedFilesSupport()
{
    return ['zip', 'txt', 'pdf'];
}

function getAllAllowedFileSupport()
{
    return ['png', 'jpg', 'jpeg', 'gif', 'zip', 'txt', 'pdf'];
}

// get conversasion data
function getChatDataSupport($data)
{
    $conversation = $data['conversation'];
    $temp = null;
    $temp['user_id'] = $conversation->sender_id;
    $temp['sender_image_link'] = isset($conversation->sender_id) ? showUserImageSupport($conversation->sender_id) : '';
    $temp['receiver_image_link'] = isset($conversation->receiver_id)? showUserImageSupport($conversation->receiver_id) : '';
    $temp['message'] = $conversation->message;
    $temp['conversation_type'] = $conversation->conversation_type;
    $temp['ticket_id'] = $conversation->ticket_id;
    $temp['has_file'] = $conversation->has_file;
    $temp['user'] = $data['user'];
    $temp['user']['photo'] = showUserImageSupport($temp['user']['id']);
    $temp['conversation_attachment'] = $data['attachment'];
    $temp['attachment'] = $data['attachment'];
    return $temp;
}

function getNotificationList()
{
    $notification = new NotificationRepository;
    $response = $notification->getNotificationList();
    if($response['success'])
    {
        $notification_list = $response['data'];
    }else{
        $notification_list = null;
    }
    return $notification_list;
}

function formatNotificationData($ticket_user_id)
{
    $notification = new NotificationRepository;
    $response = $notification->getNotificationListToSendNotification($ticket_user_id);
    $html_view = '';
    if($response['success'])
    {
        $notification_list = $response['data'];
        $total_notification = $notification_list->count();

        if (isset($notification_list))
        {
            foreach ($notification_list as $notification_item)
            {
                if($notification_item->get_notification_by == SUPPORT_NOTIFICATION_GET_BY_USER)
                {
                    $html_view .= '<div><a class="dropdown-item text-dark" href="'.route('support_notification_details', $notification_item->unique_code).'">'.$notification_item->title.'
                                    <br />
                                        <small class="p_color">'. Str::limit($notification_item->body, 30) .'</small>
                                    </a>
                                </div>
                                <hr />';
                }else{
                    $html_view .= '<div><a class="dropdown-item text-dark" href="'.route('support_agent_notification_details', $notification_item->unique_code).'" target="__blank">
                        '.$notification_item->title.'</a></div>';
                }
            }

        }

        $data['total_notification'] = $total_notification;
        $data['html_view'] = $html_view;
        $response = ['success' => true, 'message' => __('Html view of notification lisst'), 'data' => $data];

    }else{
        $data['total_notification'] = 0;
        $data['html_view'] = $html_view;
        $response = ['success' => true, 'message' => __('Html view of notification lisst'), 'data' => $data];
    }

    return $response;
}

function knowledgebaseSupportSettings($input = null)
{
    if(isset($input))
    {
        $allsettings = KnowledgebaseSupportSetting::where('slug', $input)->first();
        if(isset($allsettings))
        {
            $output = $allsettings->value;
            return $output;
        }
    }else{
        $allsettings = KnowledgebaseSupportSetting::get();
        if ($allsettings) {
            $output = [];
            foreach ($allsettings as $setting) {
                $output[$setting->slug] = $setting->value;
            }
            return $output;
        }
        return false;
    }
}

function fontawesomeIcon()
{
    $data = KnowledgebaseFontawesome::get();
    return $data;
}

function uploadAnyFileSupport($new_file, $path, $old_file_name = null)
{
    if (!Storage::disk('public')->exists($path)) {
        Storage::disk('public')->makeDirectory($path);
    }

    if (Storage::disk('public')->exists($path . $old_file_name)) {
        Storage::disk('public')->delete($path . $old_file_name);
    }


    $fileName = uniqid() . time() . '.' . $new_file->getClientOriginalExtension();
    Storage::disk('public')->put($path . $fileName, file_get_contents($new_file));

    return $fileName;
}

function socialMediaListSupport()
{
    $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
    $items = SocialMedia::where(['status' => STATUS_ACTIVE])->orderBy('id', 'desc')->get();
    if (isset($items[0])) {
        foreach ($items as $item) {
            $item->media_icon = !empty($item->media_icon) ? asset(path_image().$item->media_icon) : '';
        }
        $response = [
            'success' => true,
            'message' => __('Data get successfully'),
            'data' => $items
        ];
    } else {
        $response = [
            'success' => false,
            'message' => __('No data found'),
            'data' => []
        ];
    }
    return $response;

}

function supportTicketProjectList()
{
    $project_list = SupportProject::where('status', STATUS_ACTIVE)->latest()->get();
    return $project_list;
}

function supportCreateImageUrl($path, $imageName)
{
    $return = asset('assets/img/avater.png');
    if(isset($path) && isset($imageName))
    {
        $return = asset($path).'/'.$imageName;
    }
    return $return;
}

