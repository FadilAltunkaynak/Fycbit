<?php

namespace Modules\KnowledgeBase\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class SettingsController extends Controller
{
    public function index()
    {
        $data = knowledgebaseSupportSettings();
        if($data['logo'])
        {
            $data['logo'] = asset(FILE_KNOWLEDGE_BASE_VIEW_PATH).'/'.$data['logo'];
            
        }
        if($data['cover_image'])
        {
            $data['cover_image'] = asset(FILE_KNOWLEDGE_BASE_VIEW_PATH).'/'.$data['cover_image'];
            
        }
        return $data;
    }
}
