<?php

namespace Modules\BlogNews\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\BlogNews\Http\Services\SettingService;

class SettingController extends Controller
{
    private $service;
    public function __construct()
    {
        $this->service = new SettingService();
    }
    public function getSettings(Request $request)
    {
        try {
            $lang_key = $request->header('lang') ?? 'en';
            $api = true;
            $response = $this->service->getSettings($lang_key, $api);
            return response()->json($response);
        } catch (\Exception $e) {
            storeException('getSettings',$e->getMessage());
            return response()->json(responseData(false,__("Something went wrong")));
        }
    }
}