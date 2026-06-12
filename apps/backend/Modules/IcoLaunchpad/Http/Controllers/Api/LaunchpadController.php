<?php

namespace Modules\IcoLaunchpad\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\IcoLaunchpad\Http\Services\IcoService;
use Modules\IcoLaunchpad\Http\Services\LaunchpadFeatureService;
use Modules\IcoLaunchpad\Entities\SubmitFormLists;
use Modules\IcoLaunchpad\Entities\TokenBuyHistory;
use Modules\IcoLaunchpad\Entities\IcoPhaseInfo;
use Modules\IcoLaunchpad\Entities\IcoToken;

class LaunchpadController extends Controller
{
    private $icoService;
    private $launchpadFeatureService;
    public function __construct()
    {
        $this->icoService = new IcoService();
        $this->launchpadFeatureService = new LaunchpadFeatureService();
    }

    public function launchpadSettings()
    {
        $total_sell_token = TokenBuyHistory::get();
        $data['launchpad_cover_image'] = !empty(allsetting('launchpad_cover_image')) ? asset(FILE_ICO_VIEW_PATH . allsetting('launchpad_cover_image')) : '';
        $data['launchpad_main_image'] = !empty(allsetting('launchpad_main_image')) ? asset(FILE_ICO_VIEW_PATH . allsetting('launchpad_main_image')) : '';

        $data['launchpad_first_title'] = allsetting('launchpad_first_title');
        $data['launchpad_first_description'] = allsetting('launchpad_first_description');
        $data['launchpad_second_title'] = allsetting('launchpad_second_title');
        $data['launchpad_second_description'] = allsetting('launchpad_second_description');
        $data['launchpad_apply_to_status'] = allsetting('launchpad_apply_to_status');
        $data['launchpad_why_choose_us_text'] = allsetting('launchpad_why_choose_us_text');
        $data['launchpad_apply_to_button_text'] = allsetting('launchpad_apply_to_button_text');
        $data['project_launchpad'] = IcoToken::where('approved_status', STATUS_ACCEPTED)->count();
        $data['all_time_unique_participants'] = phaseTotalParticipated();

        $data['total_funds_raised'] = getTotalSoldTokenICO();
        $data['current_funds_locked'] = getTotalSuppliedTokenICO();
        $get_featureList_response = $this->launchpadFeatureService->getActiveFeatureList();
        if ($get_featureList_response['success'] == true) {
            $get_featureList_data = $get_featureList_response['data'];
            $get_featureList_data->map(function ($query) {
                if (isset($query->image)) {
                    $query->image = asset(FILE_ICO_VIEW_PATH . $query->image);
                }
                return $query;
            });

            $data['feature_list'] = $get_featureList_data;
        }

        $response = ['success' => true, 'message' => __('Launchpad Settings Details!'), 'data' => $data];
        return response()->json($response);
    }
}
