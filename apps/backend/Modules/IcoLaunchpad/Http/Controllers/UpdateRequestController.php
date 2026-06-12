<?php

namespace Modules\IcoLaunchpad\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\IcoLaunchpad\Http\Services\UpdateRequestService;

class UpdateRequestController extends Controller
{
    public function __construct()
    {
        $this->updateRequestService = new UpdateRequestService();
    }

    public function updateRequestTableInfo($type, $id)
    {
        $data['title'] = __('Update Request Info Details');
        $response = $this->updateRequestService->getUpdateRequestDetails($type, decrypt($id));
        if ($response['success'] == true) {
            $data['update_info_list'] = $response['data'];
        }
        return view('icolaunchpad::ico.update-request-table', $data);
    }

    public function updateRequestTableInfoAccept($id)
    {
        $response = $this->updateRequestService->updatedRequestedAcceptedByID(decrypt($id));

        return back()->with(['success' => $response['message']]);
    }

    public function updateRequestTableInfoReject($id)
    {
        $response = $this->updateRequestService->updatedRequestedRejectedByID(decrypt($id));

        return back()->with(['success' => $response['message']]);
    }
}
