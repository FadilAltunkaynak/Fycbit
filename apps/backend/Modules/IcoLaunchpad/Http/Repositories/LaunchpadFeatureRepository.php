<?php

namespace Modules\IcoLaunchpad\Http\Repositories;

use Modules\IcoLaunchpad\Entities\LaunchpadFeatureList;

class LaunchpadFeatureRepository
{

    public function findByIdFeatureDetails($id)
    {
        try {
            $data = LaunchpadFeatureList::find($id);
            $response = ['success' => true, 'message' => __('Launchpad Feature Details!'), 'data' => $data];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something Went Wrong!')];
            storeException("findByIdFeatureDetails", $e->getMessage());
        }
        return $response;
    }
    public function getFeatureList()
    {
        try {

            $data = LaunchpadFeatureList::get();
            $response = ['success' => true, 'message' => __('Launchpad Feature List!'), 'data' => $data];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something Went Wrong!')];
            storeException("getFeatureList", $e->getMessage());
        }
        return $response;
    }

    public function getActiveFeatureList()
    {
        try {

            $data = LaunchpadFeatureList::where('status', STATUS_ACTIVE)->get();
            $response = ['success' => true, 'message' => __('Launchpad Feature Active List!'), 'data' => $data];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something Went Wrong!')];
            storeException("getFeatureList", $e->getMessage());
        }
        return $response;
    }
    public function saveLaunchpadFeature($id, $data)
    {
        try {
            if (isset($id)) {
                $data = LaunchpadFeatureList::where('id', $id)->update($data);
                $response = ['success' => true, 'message' => __('Launchpad Feature is updated successfully!'), 'data' => $data];
            } else {
                $data = LaunchpadFeatureList::create($data);
                $response = ['success' => true, 'message' => __('Launchpad Feature is stored successfully!'), 'data' => $data];
            }
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Launchpad Feature is not store!')];
            storeException("saveLaunchpadFeature", $e->getMessage());
        }
        return $response;
    }

    public function launchpadFeatureStatusChange($id)
    {
        try {

            $data = LaunchpadFeatureList::find($id);
            if ($data->status == STATUS_ACTIVE) {
                $data->update(['status' => STATUS_INACTIVE]);
            } else {
                $data->update(['status' => STATUS_ACTIVE]);
            }
            $response = ['success' => true, 'message' => __('Launchpad Feature status is updated successfully!'), 'data' => $data];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Launchpad Feature status is not updated!')];
            storeException("launchpadFeatureStatusChange", $e->getMessage());
        }
        return $response;
    }

    public function deleteLaunchpadFeature($id)
    {
        try {
            LaunchpadFeatureList::where('id', $id)->delete();

            $response = ['success' => true, 'message' => __('Launchpad Feature is deleted successfully!')];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Launchpad Feature is not deleted!')];
            storeException("deleteLaunchpadFeature", $e->getMessage());
        }
        return $response;
    }
}
