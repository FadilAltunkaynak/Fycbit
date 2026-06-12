<?php

namespace Modules\IcoLaunchpad\Http\Repositories;

use App\Model\ConversationDetails;
use Modules\IcoLaunchpad\Entities\IcoToken;
use App\Http\Services\MyCommonService;

class ICOChatRepository
{

    public function getConversationList($token_id, $adminId, $userId)
    {
        $conversation_list = ConversationDetails::where(['conversation_type' => CONVERSATION_TYPE_ICO_TOKEN, 'conversation_type_id' => $token_id])
            ->where(function ($q) use ($adminId, $userId) {
                $q->where(['sender_id' => $userId, 'receiver_id' => $adminId])
                    ->orWhere(function ($u) use ($adminId, $userId) {
                        $u->where(['receiver_id' => $userId, 'sender_id' => $adminId]);
                    });
            })
            ->get();
        return $conversation_list;
    }
    public function storeICOChatConversation($data)
    {
        try {
            $user = auth()->user();
            if ($user->role == USER_ROLE_ADMIN) {
                $token_details = IcoToken::find($data['token_id']);
            } else {
                $token_details = IcoToken::where(['id' => $data['token_id'], 'user_id' => $user->id])->first();
            }
            if (isset($token_details)) {
                $new_conversation = new ConversationDetails;
                $new_conversation->sender_id = $user->id;
                $new_conversation->receiver_id = $data['receiver_id'];
                $new_conversation->conversation_type = CONVERSATION_TYPE_ICO_TOKEN;
                $new_conversation->conversation_type_id = $token_details->id;
                $new_conversation->message = $data['message'];
                $new_conversation->file_name = $data['file_name'] ?? null;
                $new_conversation->is_seen = CONVERSATION_UNSEEN;
                $new_conversation->save();
                $response = ['success' => true, 'message' => __('Message Sent successfully!'), 'data' => getChatData($new_conversation)];
                $user_id = $user->id;
                $title = __('New Message send');
                $message = $new_conversation->message ?? __('Get message from token');
                $mycommonService = new MyCommonService;
                $mycommonService->sendNotificationToUserUsingSocket($user_id, $title, $message);

                $channel_id = $new_conversation->receiver_id . '-' . $token_details->id;
                $channel_id2 = $user->id . '-' . $token_details->id;
                $channel_name = 'New-Message-' . $channel_id;
                $channel_name2 = 'New-Message-' . $channel_id2;
                $event_name = 'Conversation';
                $channel_data = $response;
                // storeException('chanel data', $channel_data);
                sendDataThroughWebSocket($channel_name, $event_name, $channel_data);
                sendDataThroughWebSocket($channel_name2, $event_name, $channel_data);
            } else {
                $response = ['success' => false, 'message' => __('Token not found!')];
            }
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("storeICOChatConversation", $e->getMessage());
        }
        return $response;
    }
}
