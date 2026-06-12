<?php

namespace Modules\IcoLaunchpad\Http\Repositories;

use Modules\IcoLaunchpad\Entities\IcoPhaseAdditional;

class IcoPhaseAdditionalRepository
{

    public function saveAddtionaPhaseInfo($data, $id = null)
    {
        try {
            if (isset($id)) {
                $data = IcoPhaseAdditional::where('id', $id)->update($data);
                $response = ['success' => true, 'message' => __('Updated ICO Phase additional info successfully!'), 'data' => $data];
            } else {
                $data = IcoPhaseAdditional::create($data);
                $response = ['success' => true, 'message' => __('Store new ICO Phase additional info successfully!'), 'data' => $data];
            }
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('ICO Phase additional info is not store!')];
            storeException("saveAddtionaPhaseInfo", $e->getMessage());
        }
        return $response;
    }

    public function saveAddtionaPhaseInfoAPI($additional_items)
    {
        try {

            if (count($additional_items) > 0) {
                foreach ($additional_items as $item) {
                    if (isset($item['id'])) {
                        $data = IcoPhaseAdditional::where('id', $item['id'])->first();
                        $data->ico_phase_id = $item['ico_phase_id'];
                        $data->title = $item['title'];
                        $data->value = $item['value'];
                        $data->file = $item['file'] ?? null;
                        $data->save();
                    } else {
                        $data = new IcoPhaseAdditional;
                        $data->ico_phase_id = $item['ico_phase_id'];
                        $data->title = $item['title'];
                        $data->value = $item['value'];
                        $data->file = $item['file'] ?? null;
                        $data->save();
                    }
                }
                $response = ['success' => true, 'message' => __('ICO Phase additional info updated successfully!')];
            } else {
                $response = ['success' => false, 'message' => __('ICO Phase additional info is not store!')];
            }
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something Went Wrong!')];
            storeException("saveAddtionaPhaseInfoAPI", $e->getMessage());
        }
        return $response;
    }

    public function deleteICOPhaseAdditionalById($id)
    {
        try {

            $data = IcoPhaseAdditional::where('id', $id)->delete();
            $response = ['success' => true, 'message' => __('ICO Phase additional info deleted successfully!')];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('ICO Phase additional info is not deleted!')];
            storeException("deleteICOPhaseAdditionalById", $e->getMessage());
        }
        return $response;
    }

    public function getDetailsOfICOPhaseAdditionalByID($id)
    {
        try {

            $data = IcoPhaseAdditional::where('ico_phase_id', $id)->get();
            $response = ['success' => true, 'message' => __('ICO Phase additional info list!'), 'data' => $data];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("getDetailsOfICOPhaseAdditionalByID", $e->getMessage());
        }
        return $response;
    }
}
