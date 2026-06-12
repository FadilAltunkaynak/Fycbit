<?php

namespace Modules\P2P\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\P2P\Http\Requests\ConversationRequest;
use Modules\P2P\Http\Service\ConversationService;

class ConversationController extends Controller
{
    private $conversationService;
    public function __construct()
    {
        $this->conversationService  = new ConversationService;
    }
    public function sendMessage(ConversationRequest $request)
    {
        $response = $this->conversationService->sendConversation($request);
        return $response;
    }
}
