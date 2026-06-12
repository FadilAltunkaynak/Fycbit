<?php

namespace Modules\BlogNews\Http\Controllers\Blog;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\BlogNews\Entities\BlogNewsSetting;
use Modules\BlogNews\Http\Services\SettingService;
use App\Http\Services\AdminLangService;
use Modules\BlogNews\Entities\SettingTranslation;

class SettingController extends Controller
{
    private $service;
    private $languageService;
    public function __construct()
    {
        $this->service = new SettingService();
        $this->languageService = new AdminLangService;
    }

    public function settingPage()
    {
        $data = [];
        $data['title'] = __("Blog Settings");
        try {
            $data['tab'] = 'feature';
            if(session()->has('tab')) $data['tab'] = session()->get('tab');
            $data['setting'] = BlogNewsSetting::get()->toSlugValue();
        } catch (\Exception $e) {
            storeException('settingPage', $e->getMessage());
        }
        return view('blognews::blog.setting',$data);

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
            storeException('settingPage', $e->getMessage());
            return redirect()->back()->with('dismiss', __("Something went wrong"));
        }

    }

    public function settingTranslatePage()
    {
        $data['title'] = __("Update Languages For Blog Settings Text");
        $language_response = $this->languageService->activeLanguageList();
        if($language_response['success'])
        {
            $data['language_list'] = $language_response['data'];
        }

        return view('blognews::blog.settings.settings-translate', $data);
    }

    public function settingTranslateUpdatePage($lang_key)
    {
        $data['title'] = __("Update Languages For Blog Settings Text");
        $data['setting'] = BlogNewsSetting::get()->toSlugValue();
        $language_details_response = $this->languageService->languageDetailsByKey($lang_key);
        if($language_details_response['success'])
        {
            $data['language_details'] = $language_details_response['data'];
            $data['setting_translation_text'] = SettingTranslation::where('lang_key',$lang_key)->get()->toSlugValue();

            return view('blognews::blog.settings.settings-translate-update', $data);
        }else{
            return back()->with(['success' => 'Invalid Request']);
        }
        
    }

    public function settingTranslateUpdateText(Request $request)
    {
        $response = $this->service->settingsLanguageTextUpdate($request);
        if($response['success'])
        {
            return back()->with(['success' => $response['message']]);
        }else{
            return back()->with(['success' => $response['message']]);
        }
    }

}
