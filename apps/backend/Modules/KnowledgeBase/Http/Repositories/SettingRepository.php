<?php
namespace Modules\KnowledgeBase\Http\Repositories;

use Modules\KnowledgeBase\Entities\KnowledgebaseSupportSetting;
use Illuminate\Support\Facades\DB;
use Modules\KnowledgeBase\Entities\KnowledgebaseFontawesome;

class SettingRepository{

    public function siteSettingsUpdate($request)
    {
        try{

            if(isset($request->logo))
            {
                KnowledgebaseSupportSetting::where('slug', 'logo')->update(['value' => uploadAnyFileSupport($request->logo, FILE_KNOWLEDGE_BASE_STORAGE_PATH, knowledgebaseSupportSettings('logo'))]);
            }

            if(isset($request->cover_image))
            {
                KnowledgebaseSupportSetting::where('slug', 'cover_image')->update(['value' => uploadAnyFileSupport($request->cover_image, FILE_KNOWLEDGE_BASE_STORAGE_PATH, knowledgebaseSupportSettings('cover_image'))]);
            }

            $response = ['success' => true, 'message' => __('Settings is updated successfully!')];

        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("siteSettingsUpdate", $e->getMessage());
        }
        return $response;
    }

    public function saveSiteTextSetting($request)
    {
        $response = ['success' => false, 'message' => __('Invalid request')];
        try{

            if (isset($request->knowledgebase)) {
                KnowledgebaseSupportSetting::updateOrCreate(['slug' => 'knowledgebase'], ['value' => $request->knowledgebase]);
            }
            if (isset($request->support)) {
                KnowledgebaseSupportSetting::updateOrCreate(['slug' => 'support'], ['value' => $request->support]);
            }
            if (isset($request->knowledgebase_page_cover_first_title)) {
                KnowledgebaseSupportSetting::updateOrCreate(['slug' => 'knowledgebase_page_cover_first_title'], ['value' => $request->knowledgebase_page_cover_first_title]);
            }
            if (isset($request->knowledgebase_page_cover_second_title)) {
                KnowledgebaseSupportSetting::updateOrCreate(['slug' => 'knowledgebase_page_cover_second_title'], ['value' => $request->knowledgebase_page_cover_second_title]);
            }
            if (isset($request->knowledgebsupport_page_cover_first_titlease)) {
                KnowledgebaseSupportSetting::updateOrCreate(['slug' => 'support_page_cover_first_title'], ['value' => $request->support_page_cover_first_title]);
            }
            if (isset($request->support_page_cover_second_title)) {
                KnowledgebaseSupportSetting::updateOrCreate(['slug' => 'support_page_cover_second_title'], ['value' => $request->support_page_cover_second_title]);
            }
            if (isset($request->create_ticket_button_text)) {
                KnowledgebaseSupportSetting::updateOrCreate(['slug' => 'create_ticket_button_text'], ['value' => $request->create_ticket_button_text]);
            }

            $response = ['success' => true, 'message' => __('Site Text are updated successfully!')];

        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("saveSiteTextSetting", $e->getMessage());
        }
        return $response;
    }

    public function siteTextReset()
    {
        try{

            KnowledgebaseSupportSetting::updateOrCreate(['slug' => 'knowledgebase'], ['value' => 'KnowledgeBase']);

            KnowledgebaseSupportSetting::updateOrCreate(['slug' => 'support'], ['value' => 'Support']);

            KnowledgebaseSupportSetting::updateOrCreate(['slug' => 'knowledgebase_page_cover_first_title'], ['value' => 'How can we help you ?']);

            KnowledgebaseSupportSetting::updateOrCreate(['slug' => 'knowledgebase_page_cover_second_title'], ['value' => 'Ask Questions Browse Articles Find Answers']);

            KnowledgebaseSupportSetting::updateOrCreate(['slug' => 'support_page_cover_first_title'], ['value' => 'How can we help you ?']);

            KnowledgebaseSupportSetting::updateOrCreate(['slug' => 'support_page_cover_second_title'], ['value' => 'If you have any problem, you can create a ticket to get support']);

            KnowledgebaseSupportSetting::updateOrCreate(['slug' => 'create_ticket_button_text'], ['value' => 'Create Ticket']);


            $response = ['success' => true, 'message' => __('Site Text are reseted successfully!')];

        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("siteTextReset", $e->getMessage());
        }
        return $response;
    }

    public function siteIconList()
    {
        $icon_list = KnowledgebaseFontawesome::latest()->get();
        $response = ['success' => true, 'message' => __('Icon List'), 'data' => $icon_list];
        return $response;
    }

    public function siteIconDetailsById($id)
    {
        $icon_details = KnowledgebaseFontawesome::find($id);
        $response = ['success' => true, 'message' => __('Icon details'), 'data' => $icon_details];
        return $response;
    }

    public function siteIconDelete($id)
    {
        $icon_details = KnowledgebaseFontawesome::find($id);
        $icon_details->delete();
        $response = ['success' => true, 'message' => __('Icon is deleted successfully!')];
        return $response;
    }

    public function saveSiteIcon($id, $data)
    {
        try{

            if($id == null)
            {
                KnowledgebaseFontawesome::create($data);
                $response = ['success' => true, 'message' => __('New Icon is created successfully!')];
            }else{
                KnowledgebaseFontawesome::where('id', $id)->update($data);
                $response = ['success' => true, 'message' => __('Icon is updated successfully!')];
            }

        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("saveSiteIcon", $e->getMessage());
        }
        return $response;
    }
}
