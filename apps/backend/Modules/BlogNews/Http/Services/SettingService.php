<?php

namespace Modules\BlogNews\Http\Services;

use Illuminate\Support\Facades\DB;
use Modules\BlogNews\Entities\SettingTranslation;
use Modules\BlogNews\Repository\SettingRepository;
use App\Http\Services\AdminLangService;

class SettingService {

    private $repo;
    private $languageService;
    public function __construct(){
        $this->repo = new SettingRepository();
        $this->languageService = new AdminLangService;
    }

    public function settingsUpdate($request)
    {
        try{
            $data = [];
            foreach($request->except(['_token','tab']) as $slug => $value){
                $data[] = [
                    'slug' => $slug ,
                    'value'=> $value
                ];
            }
            return $this->repo->settingSaveUpdate($data);
        } catch (\Exception $e) {
            storeException('getBlogPosts',$e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }
    public function getSettings($lang_key = null, $api = false)
    {
        try{
            return $this->repo->getSettings($lang_key, $api);
        } catch (\Exception $e) {
            storeException('getSettings',$e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    public function settingsLanguageTextUpdate($request)
    {
        $lang_key = $request->lang_key ?? '';
        $language_details_response = $this->languageService->languageDetailsByKey($lang_key);
        if($language_details_response['success'])
        {
            if(isset($request->blog_feature_heading) &&
                isset($request->blog_feature_heading['en']) && 
                isset($request->blog_feature_heading[$lang_key]))
            {
                SettingTranslation::updateOrCreate(
                    ['lang_key' => $lang_key,'slug'=>'blog_feature_heading', 'key' => $request->blog_feature_heading['en']],
                    ['value' => $request->blog_feature_heading[$lang_key]]
                );
            }

            if(isset($request->blog_feature_description) &&
                isset($request->blog_feature_description['en']) && 
                isset($request->blog_feature_description[$lang_key]))
            {
                SettingTranslation::updateOrCreate(
                    ['lang_key' => $lang_key,'slug'=>'blog_feature_description' , 'key' => $request->blog_feature_description['en']],
                    ['value' => $request->blog_feature_description[$lang_key]]
                );
            }

            $response = ['success' => true, 'message' => __('Blog Settings Text updated')];
        }else{
            $response = ['success' => false, 'message' => __('Invalid Request')];
        }

        return $response;
    }
}