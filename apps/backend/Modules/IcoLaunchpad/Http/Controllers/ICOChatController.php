<?php

namespace Modules\IcoLaunchpad\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\IcoLaunchpad\Http\Services\IcoService;
use Modules\IcoLaunchpad\Http\Services\ICOChatService;

class ICOChatController
{
    private $icoService;
    private $icoChatService;
    public function __construct()
    {
        $this->icoChatService = new ICOChatService();
        $this->icoService = new IcoService();
    }

    public function getICOChatDetails(Request $request)
    {
        $data['title'] = __('Chat with ICO Token owner');
        $data['token_id'] = decrypt($request->id);

        $get_ico_token = $this->icoService->findICOTokenByID(decrypt($request->id));
        if ($get_ico_token['success'] == true) {
            $ico_token_details = $get_ico_token['data']['ico_phase_token'];
            $data['ico_token_details'] = $ico_token_details;
            $data['sender_id'] = $ico_token_details->id;
        } else {
            return back()->with(['dismiss' => $get_ico_token['message']]);
        }

        $data['sender_id'] =
            $data['conversation_list'] = $this->icoChatService->getConversationList(decrypt($request->id), Auth::id(), $ico_token_details->user_id);
        //        dd($data['conversation_list']);
        return view('icolaunchpad::ico.chat.index', $data);
    }

    public function getICOChatConversationStore(Request $request)
    {
        $response = $this->icoChatService->storeICOChatConversation($request);

        return response()->json($response);
    }
}
