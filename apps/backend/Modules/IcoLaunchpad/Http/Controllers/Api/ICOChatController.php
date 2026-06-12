<?php

namespace Modules\IcoLaunchpad\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\IcoLaunchpad\Http\Services\ICOChatService;
use App\User;
use Modules\IcoLaunchpad\Http\Requests\IcoChatRequest;

class ICOChatController
{
    private $icoChatService;
    public function __construct()
    {
        $this->icoChatService = new ICOChatService();
    }

    public function getICOChatDetails(Request $request)
    {
        if (!empty($request->token_id)) {
            $admin_list = User::where('role', USER_ROLE_ADMIN)->select('id', 'first_name', 'last_name', 'photo')->get();
            $adminId = $request->admin_id ?? $admin_list[0]->id;
            $conversation_list = $this->icoChatService->getConversationList($request->token_id, $adminId, Auth::id());
            $admin_list->map(function ($query) {
                $query->name = $query->first_name . ' ' . $query->last_name;
                if (isset($query->photo)) {
                    $query->photo = asset(IMG_USER_PATH . $query->photo);
                }
            });
            $data['conversation_list'] = $conversation_list;
            $data['admin_list'] = $admin_list;
            $response = ['success' => true, 'message' => __('Conversation List'), 'data' => $data];
        } else {

            $response = ['success' => false, 'message' => __('Token id is required!')];
        }

        return response()->json($response);
    }

    public function getICOChatConversationStore(IcoChatRequest $request)
    {
        if (!empty($request->token_id)) {
            $response = $this->icoChatService->storeICOChatConversation($request);
        } else {

            $response = ['success' => false, 'message' => __('Token id is required!')];
        }

        return response()->json($response);
    }
}
