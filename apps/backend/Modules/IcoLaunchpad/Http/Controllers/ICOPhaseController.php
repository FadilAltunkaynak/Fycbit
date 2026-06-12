<?php

namespace Modules\IcoLaunchpad\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\IcoLaunchpad\Http\Services\IcoService;
use Modules\IcoLaunchpad\Http\Services\IcoPhaseService;
use Modules\IcoLaunchpad\Http\Services\IcoPhaseAdditionalService;
use App\Model\Coin;
use Modules\IcoLaunchpad\Http\Requests\IcoPhaseRequest;

use Modules\IcoLaunchpad\Entities\IcoPhaseInfo;

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

    public function listICOPhase($icoTokenId)
    {
        $data['title'] = __('ICO Token Phase List');
        $response = $this->icoPhaseService->getICOPhasesList(decryptId($icoTokenId));
        if ($response['success'] == true) {
            $data['ico_phases_list'] = $response['data'];
        }
        return view('icolaunchpad::ico.ico-phase-list', $data);
    }

    public function addNewICOPhase($icoTokenId)
    {
        $data['title'] = __('Add New Phase for ICO Token');
        $data['coins'] = Coin::where(['is_base' => STATUS_ACTIVE, 'trade_status' => STATUS_ACTIVE, 'status' => STATUS_ACTIVE])->get();
        if ($icoTokenId) {
            $response = $this->icoService->findICOTokenByID(decryptId($icoTokenId));
            if ($response['success'] == true) {
                $data['ico_token'] = $response['data']['ico_phase_token'] ?? null;
                if ($data['ico_token']->user_id != auth()->user()->id) {
                    return back()->with(['dismiss' => __('You have no access to create ICO phase for this ICO token!')]);
                }
            }
        }

        return view('icolaunchpad::ico.add-ico-phase', $data);
    }

    public function saveICOPhase(IcoPhaseRequest $request)
    {
        $checkDateResponse = $this->icoPhaseService->checkDateOfPhase($request);
        if ($checkDateResponse['success'] != true) {
            return back()->with(['success' => $checkDateResponse['message']])
                ->withInput($request->input());
        }

        $response = $this->icoPhaseService->saveICOPhase($request);
        return redirect()->route('listICOPhase', ['icoTokenId' => encrypt($request->ico_token_id)])->with(['success' => $response['message']]);
    }

    public function editICOPhase($icoPhaseId)
    {
        $data['title'] = __('Update Phase for ICO Token');
        $data['coins'] = Coin::where(['is_base' => STATUS_ACTIVE, 'trade_status' => STATUS_ACTIVE, 'status' => STATUS_ACTIVE])->get();
        $response = $this->icoPhaseService->getICOPhaseDetailsById(decryptId($icoPhaseId));
        if ($response['success'] == true) {
            $data['item'] = $response['data'];
        }
        return view('icolaunchpad::ico.add-ico-phase', $data);
    }

    public function deleteICOPhase($icoPhaseId)
    {
        $response = $this->icoPhaseService->deleteICOPhaseById(decryptId($icoPhaseId));

        return back()->with(['dismiss' => $response['message']]);
    }

    public function deleteICOPhaseAdditionalInfo($id)
    {
        $response = $this->icoPhaseAdditionalService->deleteICOPhaseAdditionalById(decryptId($id));

        return back()->with(['dismiss' => $response['message']]);
    }

    public function saveICOPhaseFeatured(Request $request)
    {
        $response = $this->icoPhaseService->changeICOPhaseFeatured($request->id);

        return response()->json($response);
    }

    public function saveICOPhaseStatus(Request $request)
    {
        $response = $this->icoPhaseService->changeICOPhaseStatus($request->id);

        return response()->json($response);
    }
}
