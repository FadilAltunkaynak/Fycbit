<?php

namespace Modules\IcoLaunchpad\Http\Services;

use Modules\IcoLaunchpad\Http\Repositories\UpdateRequestRepository;

class UpdateRequestService
{

    private $updateRequestRepository;

    public function __construct()
    {
        $this->updateRequestRepository = new UpdateRequestRepository();
    }

    public function getUpdateRequestDetails($type, $id)
    {
        $response = $this->updateRequestRepository->getUpdateRequestDetails($type, $id);
        return $response;
    }

    public function updatedRequestedAcceptedByID($id)
    {
        $response = $this->updateRequestRepository->updatedRequestedAcceptedByID($id);
        return $response;
    }

    public function updatedRequestedRejectedByID($id)
    {
        $response = $this->updateRequestRepository->updatedRequestedRejectedByID($id);
        return $response;
    }
}
