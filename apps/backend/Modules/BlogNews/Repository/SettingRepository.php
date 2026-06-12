<?php

namespace Modules\BlogNews\Repository;

use Illuminate\Support\Facades\DB;
use Modules\BlogNews\Entities\BlogNewsSetting;

class SettingRepository {
    public function settingSaveUpdate($data)
    {
        try{
            $success = false;
            if(is_array($data)){
                if(isset($data[0]) && is_array($data[0])){
                    $success = $this->process($data,true);
                }else{
                    $success = $this->process($data);
                }
            }
            if($success) return responseData(true, __("Setting updated successfully"));
            return responseData(false, __("Setting updated failed"));
        } catch (\Exception $e) {
            storeException('newsSave repo',$e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function process($data,$multi = 0)
    {
        try{
            if(!$multi) $data[] = $data;
            $success = true;
            foreach($data as $setting){
                $data = BlogNewsSetting::updateOrCreate(['slug' => $setting['slug']],['value'=>$setting['value']]);
                if(!$data) $success = false;
            }
            return $success;
        }catch (\Exception $e){
            storeException('process repo',$e->getMessage());
            return false;
        }
    }
    public function getSettings($lang_key = null, $api = false)
    {
        // ->toSlugValue()
        try{
            $setting_data_list = BlogNewsSetting::with(['translationSettings'=>function($query)use($lang_key){
                $query->where('lang_key', $lang_key);
            }])->get();

            if($api)
            {
                $setting_data_list->map(function ($query) {
                    if ($query->translationSettings->count() > 0) {
                        $query->value = $query->translationSettings[0]->value;
                    }
                });
            }
            $data = [];
            foreach($setting_data_list as $setting_data )
            {
                $data[$setting_data->slug] = $setting_data->value;
            }
            return responseData(true, __("Settings get successfully"), $data);
        }catch (\Exception $e){
            storeException('getSettings repo',$e->getMessage());
            return false;
        }
    }
}