<?php

namespace Modules\BlogNews\Http\Controllers\News;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\BlogNews\Entities\BlogNewsSetting;
use Modules\BlogNews\Http\Services\SettingService;

class SettingController extends Controller
{
    private $service;
    public function __construct()
    {
        $this->service = new SettingService();
    }

    public function settingPage()
    {
        $data = [];
        try {
            $data['title'] = __("News Setting");
            $data['tab'] = 'comment';
            if(session()->has('tab')) $data['tab'] = session()->get('tab');
            $data['setting'] = BlogNewsSetting::get()->toSlugValue();
        } catch (\Exception $e) {
            storeException('settingPage news', $e->getMessage());
        }
        return view('blognews::news.setting',$data);

    }

    public function settingUpdate(Request $request)
    {
        $data = [];
        try {
            $tab = 'comment';
            if(isset($request->tab)) $tab = $request->tab;
            $rsponse = $this->service->settingsUpdate($request);
            if($rsponse['success'])
                return redirect()->back()->with(['tab' => $tab,'success' => $rsponse['message']]);
            return redirect()->back()->with(['tab' => $tab,'dismiss' => $rsponse['message']]);
        } catch (\Exception $e) {
            storeException('news settingPage', $e->getMessage());
            return redirect()->back()->with('dismiss', __("Something went wrong"));
        }
    }

}
