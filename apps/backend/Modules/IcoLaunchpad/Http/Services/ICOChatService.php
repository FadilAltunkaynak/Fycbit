<?php

namespace Modules\IcoLaunchpad\Http\Services;

use Modules\IcoLaunchpad\Http\Repositories\ICOChatRepository;

class ICOChatService
{

    private $icoChatRepository;

    public function __construct()
    {
        $this->icoChatRepository = new ICOChatRepository();
    }

    public function getConversationList($token_id, $adminId, $userId)
    {
        $response = $this->icoChatRepository->getConversationList($token_id, $adminId, $userId);
        $data = [];
        if (isset($response[0])) {
            foreach ($response as $item) {
                $data[] = getChatData($item);
            }
        }
        return $data;
    }

    public function storeICOChatConversation($request)
    {
        try {
            $data = [
                'token_id' => $request->token_id,
                'message' => $request->message ?? '',
                'receiver_id' => $request->receiver_id
            ];

            if (!empty($request->file)) {
                $imageName = uploadAnyFile($request->file, FILE_ICO_CHAT_STORAGE_PATH);
                $data['file_name'] = $imageName;
            }

            $response = $this->icoChatRepository->storeICOChatConversation($data);
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("storeICOChatConversation", $e->getMessage());
        }
        return $response;
    }
}
