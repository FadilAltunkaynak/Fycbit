<?php

namespace Modules\P2P\Http\Repository;

use App\Model\AdminSetting;
use Illuminate\Support\Facades\DB;

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
            storeException('settingSaveUpdate p2p repo',$e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function process($data,$multi = 0)
    {
        try{
            if(!$multi) $data[] = $data;
            $success = true;
            foreach($data as $setting){
                $data = AdminSetting::updateOrCreate(['slug' => $setting['slug']],['value'=>$setting['value']]);
                if(!$data) $success = false;
            }
            return $success;
        }catch (\Exception $e){
            storeException('process p2p repo',$e->getMessage());
            return false;
        }
    }
}
