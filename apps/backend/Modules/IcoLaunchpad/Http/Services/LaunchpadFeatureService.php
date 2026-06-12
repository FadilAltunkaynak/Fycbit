<?php

namespace Modules\IcoLaunchpad\Http\Services;

use Modules\IcoLaunchpad\Http\Repositories\LaunchpadFeatureRepository;
use Modules\IcoLaunchpad\Entities\LaunchpadFeatureList;
use Illuminate\Support\Str;

class LaunchpadFeatureService
{

    private $launchpadFeatureRepository;

    public function __construct()
    {
        $this->launchpadFeatureRepository = new LaunchpadFeatureRepository();
    }

    public function getFeatureList()
    {
        $response =  $this->launchpadFeatureRepository->getFeatureList();

        return $response;
    }

    public function getActiveFeatureList()
    {
        $response =  $this->launchpadFeatureRepository->getActiveFeatureList();

        return $response;
    }

    public function saveLaunchpadFeature($request)
    {
        try {
            $data = [
                'title' => $request->title,
                'description' => $request->description,
                'slug' => Str::slug($request->title),
                'page_type' => $request->page_type,
                'page_link' => $request->page_link,
                'custom_page_description' => $request->custom_page_description
            ];

            $id = null;
            $oldImage = null;
            if (isset($request->id)) {
                $id = $request->id;
                $launchpadFeatureDetails = LaunchpadFeatureList::find($id);
                if (isset($launchpadFeatureDetails)) {
                    $oldImage = $launchpadFeatureDetails->image;
                }
            }

            if ($request->image) {
                $imageName = uploadAnyFile($request->image, FILE_ICO_STORAGE_PATH, $oldImage);
                $data['image'] = $imageName;
            }

            $response = $this->launchpadFeatureRepository->saveLaunchpadFeature($id, $data);
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something Went Wrong!')];
            storeException("saveLaunchpadFeature", $e->getMessage());
        }
        return $response;
    }

    public function launchpadFeatureStatusChange($id)
    {
        $response = $this->launchpadFeatureRepository->launchpadFeatureStatusChange($id);
        return $response;
    }

    public function findByIdFeatureDetails($id)
    {
        $response = $this->launchpadFeatureRepository->findByIdFeatureDetails($id);
        return $response;
    }

    public function deleteLaunchpadFeature($id)
    {
        $response = $this->launchpadFeatureRepository->deleteLaunchpadFeature($id);
        return $response;
    }
}
