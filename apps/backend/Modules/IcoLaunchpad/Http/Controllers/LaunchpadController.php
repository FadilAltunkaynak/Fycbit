<?php

namespace Modules\IcoLaunchpad\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Http\Services\AdminSettingService;
use Modules\IcoLaunchpad\Http\Services\LaunchpadFeatureService;
use Modules\IcoLaunchpad\Http\Requests\FeatureRequest;

class LaunchpadController extends Controller
{
    public $settingService;
    private $launchpadFeatureService;
    public function __construct()
    {
        $this->settingService = new AdminSettingService();
        $this->launchpadFeatureService = new LaunchpadFeatureService();
    }

    public function launchpadPageSettings()
    {
        $data['title'] = __('Launchpad Settings');
        $data['launchpad_cover_image'] = allsetting('launchpad_cover_image');
        $data['launchpad_main_image'] = allsetting('launchpad_main_image');
        $data['launchpad_first_title'] = allsetting('launchpad_first_title');
        $data['launchpad_first_description'] = allsetting('launchpad_first_description');
        $data['launchpad_second_title'] = allsetting('launchpad_second_title');
        $data['launchpad_second_description'] = allsetting('launchpad_second_description');
        $data['launchpad_apply_to_status'] = allsetting('launchpad_apply_to_status');
        $data['launchpad_why_choose_us_text'] = allsetting('launchpad_why_choose_us_text');
        $data['launchpad_apply_to_button_text'] = allsetting('launchpad_apply_to_button_text');
        return view('icolaunchpad::launchpad.page-settings', $data);
    }

    public function launchpadPageSettingsUpdate(Request $request)
    {
        try {
            $data =
                [
                    'launchpad_first_title' => $request->launchpad_first_title,
                    'launchpad_first_description' => $request->launchpad_first_description,
                    'launchpad_second_title' => $request->launchpad_second_title,
                    'launchpad_second_description' => $request->launchpad_second_description,
                    'launchpad_apply_to_status' => $request->launchpad_apply_to_status,
                    'launchpad_apply_to_button_text' => $request->launchpad_apply_to_button_text,
                    'launchpad_why_choose_us_text' => $request->launchpad_why_choose_us_text,
                ];
            if (!empty($request->image)) {
                $old_img = allsetting('launchpad_cover_image');
                $imageName = uploadAnyFile($request->image, FILE_ICO_STORAGE_PATH, $old_img);
                $data['launchpad_cover_image'] = $imageName;
            }
            if (!empty($request->main_image)) {
                $old_img = allsetting('launchpad_main_image');
                $imageName = uploadAnyFile($request->main_image, FILE_ICO_STORAGE_PATH, $old_img);
                $data['launchpad_main_image'] = $imageName;
            }
            $response = $this->settingService->generalSetting($data);
        } catch (\Exception $e) {
            storeException("launchpadPageSettingsUpdate", $e->getMessage());
        }
        return redirect()->route('launchpadPageSettings')->with(['success' => $response['message']]);
    }

    public function launchpadFeatureList()
    {
        $data['title'] = __('Launchpad Feature List');

        $response = $this->launchpadFeatureService->getFeatureList();
        if ($response['success'] == true) {
            $data['feature_list'] = $response['data'];
        }

        return view('icolaunchpad::launchpad.feature-list', $data);
    }

    public function launchpadFeatureSettings()
    {
        $data['title'] = __('Launchpad Feature Settings');

        return view('icolaunchpad::launchpad.feature-settings', $data);
    }

    public function launchpadFeatureSettingsSave(FeatureRequest $request)
    {
        $response = $this->launchpadFeatureService->saveLaunchpadFeature($request);

        return redirect()->route('launchpadFeatureList')->with(['success' => $response['message']]);
    }

    public function launchpadFeatureStatus(Request $request)
    {
        $response = $this->launchpadFeatureService->launchpadFeatureStatusChange($request->id);

        return response()->json(['message' => $response['message']]);
    }

    public function launchpadFeatureSettingsEdit($id)
    {
        $data['title'] = __('Launchpad Feature Update');
        $response = $this->launchpadFeatureService->findByIdFeatureDetails(decrypt($id));

        if ($response['success'] == true) {
            $data['item'] = $response['data'];
        }

        return view('icolaunchpad::launchpad.feature-settings', $data);
    }

    public function launchpadFeatureSettingsDelete($id)
    {
        $response = $this->launchpadFeatureService->deleteLaunchpadFeature(decrypt($id));

        return redirect()->route('launchpadFeatureList')->with(['success' => $response['message']]);
    }
}
