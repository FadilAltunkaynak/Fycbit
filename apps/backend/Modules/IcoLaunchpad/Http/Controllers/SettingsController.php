<?php

namespace Modules\IcoLaunchpad\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Http\Services\AddonService;

class SettingsController extends Controller
{
    private $service;
    public function __construct()
    {
        $this->service = new AddonService();
    }

    public function index()
    {
        $data['title'] = __('Settings For ICO');
        $data['settings'] = settings();
        return view('icolaunchpad::settings.index', $data);
    }

    public function settingsSave(Request $request)
    {
        try {
            $response = $this->service->saveAddonSetting($request);
            if ($response['success'] == true) {
                return back()->with('success', $response['message']);
            } else {
                return back()->with('dismiss', $response['message']);
            }
        } catch(\Exception $e) {
            storeException('settingsSave',$e->getMessage());
            return back()->with(['dismiss' => $e->getMessage()]);
        }
    }
}
