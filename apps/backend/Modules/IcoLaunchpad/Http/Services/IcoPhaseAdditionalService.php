<?php

namespace Modules\IcoLaunchpad\Http\Services;

use Modules\IcoLaunchpad\Http\Repositories\IcoPhaseAdditionalRepository;
use Modules\IcoLaunchpad\Entities\IcoPhaseAdditional;

class IcoPhaseAdditionalService
{

    private $icoPhaseAdditionalRepository;

    public function __construct()
    {
        $this->icoPhaseAdditionalRepository = new IcoPhaseAdditionalRepository();
    }

    public function saveAddtionaPhaseInfo($data)
    {
    }

    public function deleteICOPhaseAdditionalById($id)
    {
        $response = $this->icoPhaseAdditionalRepository->deleteICOPhaseAdditionalById($id);

        return $response;
    }
    public function saveICOPhaseAdditionalAPI($request)
    {
        $size = sizeof($request->titles);
        $ids = $request->ids;
        $ico_phase_id = $request->ico_phase_id;
        $titles = $request->titles;
        $values = $request->values;
        $file_values = $request->file_values;

        $additional_items = [];

        for ($i = 0; $i < $size; $i++) {
            $data = [];
            $old_image = null;
            if (isset($ids[$i])) {
                $data['id'] = $ids[$i];
                $additionalDetails = IcoPhaseAdditional::find($ids[$i]);
                $old_image = isset($additionalDetails) ?  $additionalDetails->file : null;
            }

            $data['ico_phase_id'] = $ico_phase_id;
            $data['title'] = $titles[$i];
            $data['value'] = $values[$i];

            if (isset($file_values[$i]) && file_exists($file_values[$i])) {
                $fileName = uploadAnyFile($file_values[$i], FILE_ICO_STORAGE_PATH, $old_image);
                $data['file'] = $fileName;
            }
            array_push($additional_items, $data);
        }

        $response = $this->icoPhaseAdditionalRepository->saveAddtionaPhaseInfoAPI($additional_items);
        return $response;
    }

    public function getDetailsOfICOPhaseAdditional($id)
    {
        $response = $this->icoPhaseAdditionalRepository->getDetailsOfICOPhaseAdditionalByID($id);
        return $response;
    }
}
