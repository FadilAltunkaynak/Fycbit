<?php

namespace Modules\BlogNews\Repository;

use Illuminate\Support\Facades\DB;
use Modules\BlogNews\Entities\CustomBlogNewsPage;

class CustomPageRepository {
    public function getEditPageData($type,$id)
    {
        try{
            $data = CustomBlogNewsPage::whereId($id)->whereType($type)->first();
            if($data) return responseData(true, __("Custom page get successfully"), $data);
            return responseData(false, __("Custom page not found"));
        } catch (\Exception $e) {
            storeException('getEditPageData repo',$e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function addEditCustomPage($data,$id)
    {
        try{
            $responseErr = responseData(false, __('Custom page created failed'));
            $responseSuc = responseData(true, __('Custom page created successfully'));
            if($id){
                $responseErr = responseData(false, __('Custom page update failed'));
                $responseSuc = responseData(true, __('Custom page update successfully'));
            }
            $data = CustomBlogNewsPage::updateOrCreate(['id' => $id],$data);
            if($data) return $responseSuc;
            return $responseErr;
        } catch (\Exception $e) {
            storeException('getEditPageData repo',$e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function customPagesDelete($type,$id)
    {
        try{
            $responseErr = responseData(false, __('Custom page deleted failed'));
            $responseSuc = responseData(true, __('Custom page deleted Successful'));
            $data = CustomBlogNewsPage::where(['id' => $id,'type' => $type])->delete();
            if($data) return $responseSuc;
            return $responseErr;
        } catch (\Exception $e) {
            storeException('getEditPageData repo',$e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
}