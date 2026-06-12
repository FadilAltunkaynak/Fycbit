<?php
namespace Modules\P2P\Http\Service;

use Modules\P2P\Entities\POrder;
use Modules\P2P\Entities\POrderChat;
use Modules\P2P\Entities\POrderDispute;

class ConversationService {

    public function sendConversation($request)
    {
        $response = responseData(false,__('Something went wrong'));
        try {
            $order = POrder::where('uid', $request->order_uid)->first();
            if(isset($order)) {
                $channel_id3 = '';
                $dispute = '';
                $assignedAdmin = NULL;
                if ($order->is_reported) {
                    $dispute = POrderDispute::where(['order_id' => $order->id])->first();
                    if (!empty($dispute)) {
                        $assignedAdmin = $dispute->assigned_admin ?? NULL;
                    }
                }
                $user = auth()->user();
                $sender_id = $user->id;
                $conversation = new POrderChat;
                if (!empty($dispute)) {
                    $conversation->dispute_id = $dispute->id;
                    if ($user->role == USER_ROLE_ADMIN) {
                        $receiver_id = $order->buyer_id;
                        $channel_id2 = $receiver_id . '-' . $order->uid;
                        $channel_id3 = $order->seller_id . '-' . $order->uid;
                    } else {
                        $receiver_id = ($sender_id == $order->buyer_id)? $order->seller_id: $order->buyer_id;
                        $channel_id2 = $receiver_id . '-' . $order->uid;
                        if (!empty($assignedAdmin)) {
                            $channel_id3 = $assignedAdmin . '-' . $order->uid;
                        }
                    }
                } else {
                    $receiver_id = ($sender_id == $order->buyer_id)? $order->seller_id: $order->buyer_id;
                    $channel_id2 = $receiver_id . '-' . $order->uid;
                }

                $conversation->sender_id = $sender_id;
                $conversation->receiver_id = $receiver_id;
                $conversation->order_id = $order->id;
                $conversation->message = $request->message;

                if ($request->hasFile('file')) {
                    $imageName = uploadAnyFileP2P($request->file, CONVERSATION_ATTACHMENT_PATH);
                    $conversation->file = $imageName;
                }
                $conversation->save();

                $data['user'] = $user;
                $data['conversation'] = $conversation;
                $data['conversation']['sender_id'] = $sender_id;
                $data['conversation']['receiver_id'] = $receiver_id;
                $response = ['success' => true, 'message' => __('Message is sent successfully'),'data'=>getChatDataP2P($data)];
                $channel_id = $sender_id . '-' . $order->uid;

                $channel_name = 'New-Message-' . $channel_id;
                $channel_name2 = 'New-Message-' . $channel_id2;
                $event_name = 'Conversation';
                $channel_data = $response;
                sendDataThroughWebSocket($channel_name, $event_name, $channel_data);
                sendDataThroughWebSocket($channel_name2, $event_name, $channel_data);
                if (!empty($channel_id3)) {
                    $channel_name3 = 'New-Message-' . $channel_id3;
                    sendDataThroughWebSocket($channel_name3, $event_name, $channel_data);
                }

            } else {
                $response = responseData(false,__('Order not found'));
            }
        } catch(\Exception $e) {
            storeException('sendConversation ex', $e->getMessage());
        }

        return $response;
    }

    public function getConversationListForDisputeOrder($order_id, $dispute_id)
    {
        $conversation_list = POrderChat::where('order_id',$order_id)
                                        ->where('dispute_id',$dispute_id)
                                        ->with(['receiver','user'])->get();
        $response = ['success'=>true, 'message'=>__('Dispute order conversation list'), 'data'=>$conversation_list];

        return $response;
    }
}
