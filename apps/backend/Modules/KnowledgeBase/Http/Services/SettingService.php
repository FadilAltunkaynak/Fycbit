<?php
namespace Modules\KnowledgeBase\Http\Services;

use Modules\KnowledgeBase\Http\Repositories\SettingRepository;
class SettingService{

    private $settingRepository;
    public function __construct()
    {
        $this->settingRepository = new SettingRepository;
    }

    public function siteSettingsUpdate($request)
    {
        $response = $this->settingRepository->siteSettingsUpdate($request);
        return $response;
    }

    public function saveSiteTextSetting($request)
    {
        $response = $this->settingRepository->saveSiteTextSetting($request);
        return $response;
    }

    public function siteTextReset()
    {
        $response = $this->settingRepository->siteTextReset();
        return $response;
    }

    public function siteIconList()
    {
        $response = $this->settingRepository->siteIconList();
        return $response;
    }

    public function siteIconDetailsById($id)
    {
        $response = $this->settingRepository->siteIconDetailsById($id);
        return $response;
    }

    public function saveSiteIcon($request)
    {
        $data = [
            'title' => $request->title,
            'class_name' => $request->class_name,
            'unicode' => $request->unicode
        ];
        $id = isset($request->id) ? $request->id : null;

        $response = $this->settingRepository->saveSiteIcon($id, $data);
        return $response;
    }

    public function siteIconDelete($id)
    {
        $response = $this->settingRepository->siteIconDelete($id);
        return $response;
    }
}