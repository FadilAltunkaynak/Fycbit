<?php

namespace Modules\P2P\Http\Controllers;

use App\Model\AdminSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\P2P\Http\Service\SettingService;

class SettingController extends Controller
{
    private $service;

    public function __construct()
    {
        $this->service =  new SettingService();
    }

    public function settingPage()
    {
        $data = [];
        $data['title'] = __("Settings");
        try {
            $data['tab'] = 'condition';
            if(session()->has('tab')) $data['tab'] = session()->get('tab');
            $data['setting'] = AdminSetting::get()->toSlugValueP2P();
        } catch (\Exception $e) {
            storeException('settingPage',$e->getMessage());
        }
        return view('p2p::setting.setting',$data);
    }

    public function settingUpdate(Request $request)
    {
        $data = [];
        try {
            $tab = 'condition';
            if(isset($request->tab)) $tab = $request->tab;
            $rsponse = $this->service->settingsUpdate($request);
            if($rsponse['success'])
                return redirect()->back()->with(['tab' => $tab,'success' => $rsponse['message']]);
            return redirect()->back()->with(['tab' => $tab,'dismiss' => $rsponse['message']]);
        } catch (\Exception $e) {
            storeException('settingPage p2p controller', $e->getMessage());
            return redirect()->back()->with('dismiss', __("Something went wrong"));
        }

    }

}
