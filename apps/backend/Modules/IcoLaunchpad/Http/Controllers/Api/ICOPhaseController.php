<?php

namespace Modules\IcoLaunchpad\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\IcoLaunchpad\Http\Services\IcoService;
use Modules\IcoLaunchpad\Http\Services\IcoPhaseService;
use Modules\IcoLaunchpad\Http\Services\IcoPhaseAdditionalService;
use App\Model\Coin;
use Modules\IcoLaunchpad\Http\Requests\IcoPhaseRequest;
use Modules\IcoLaunchpad\Http\Requests\IcoPhaseAdditionalRequest;

class ICOPhaseController extends Controller
{
    private $icoService;
    private $icoPhaseService;
    private $icoPhaseAdditionalService;

    public function __construct()
    {
        $this->icoService = new IcoService();
        $this->icoPhaseService = new IcoPhaseService();
        $this->icoPhaseAdditionalService = new IcoPhaseAdditionalService();
    }

    public function getICOPhaseActiveList(Request $request)
    {

        $response = $this->icoPhaseService->getICOPhaseActiveList($request);

        return response()->json($response);
    }

    public function getActiveICOPhaseDetails(Request $request)
    {
        $extra = [
            'api'=>true,
            'lang_key'=>$request->header('lang') ?? 'en'
        ];
        $response = $this->icoPhaseService->getActiveICOPhaseDetails($request, $extra);

        return response()->json($response);
    }

    public function getICOTokenPhaseList(Request $request)
    {
        if (isset($request->ico_token_id)) {
            $response = $this->icoPhaseService->getICOPhasesList($request->ico_token_id, $request->per_page);
        } else {
            $response = ['success' => false, 'message' => __('ICO Token id is required')];
        }

        return response()->json($response);
    }

    public function getDetailsOfICOTokenPhase(Request $request)
    {
        if (isset($request->id)) {
            $ico_phase_response = $this->icoPhaseService->getICOPhaseDetailsById($request->id);
            if ($ico_phase_response['success'] == true) {
                $data = $ico_phase_response['data'];
                $data->image = isset($data->image) ? asset(FILE_ICO_VIEW_PATH . $data->image) : $data->image;

                $response = ['success' => false, 'message' => __('ICO Token Phase Details'), 'data' => $data];
            }
        } else {
            $response = ['success' => false, 'message' => __('ico_token_phase_id is required')];
        }

        return response()->json($response);
    }

    public function storeUpdateICOTokenPhase(IcoPhaseRequest $request)
    {
        $checkDateResponse = $this->icoPhaseService->checkDateOfPhase($request);
        if ($checkDateResponse['success'] == false) {
            return response()->json($checkDateResponse);
        }

        $response = $this->icoPhaseService->saveICOPhaseAPI($request);

        return response()->json($response);
    }

    public function storeUpdateICOTokenPhaseAdditional(IcoPhaseAdditionalRequest $request)
    {
        $response = $this->icoPhaseAdditionalService->saveICOPhaseAdditionalAPI($request);

        return response()->json($response);
    }

    public function getDetailsOfICOPhaseAdditional(Request $request)
    {
        $response = $this->icoPhaseAdditionalService->getDetailsOfICOPhaseAdditional($request->id);
        if ($response['success'] == true) {
            $data = $response['data'];
            $data->map(function ($query) {
                if (isset($query->file)) {
                    $query->file = asset(FILE_ICO_VIEW_PATH . $query->file);
                }
            });
        }

        return response()->json($response);
    }

    public function saveICOPhaseStatus(Request $request)
    {
        $response = $this->icoPhaseService->changeICOPhaseStatus($request->id);

        return response()->json($response);
    }
}
