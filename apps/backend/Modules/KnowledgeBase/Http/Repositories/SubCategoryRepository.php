<?php

namespace Modules\KnowledgeBase\Http\Repositories;

use Modules\KnowledgeBase\Entities\KnbArticle;
use Modules\KnowledgeBase\Entities\KnbSubCategory;

class SubCategoryRepository {

    public function getSubCategoryList()
    {
        try{
            $sub_category_list = KnbSubCategory::with('category')->latest()->get();
            $response = ['success' => true, 'message' => __('Sub Category List'), 'data' => $sub_category_list];
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("getSubCategoryList", $e->getMessage());
        }

        return $response;
    }

    public function getSubCategoryDetails($unique_code)
    {
        try{
            $sub_category_details = KnbSubCategory::where('unique_code', $unique_code)->first();
            $response = ['success' => true, 'message' => __('Sub Category details'), 'data' => $sub_category_details];
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("getSubCategoryDetails", $e->getMessage());
        }

        return $response;
    }

    public function storeUpdateSubCategory($unique_code,$data)
    {
        try{
            if($unique_code !=null)
            {
                $category = KnbSubCategory::where('unique_code', $unique_code)->first();
                $category->update($data);
                $response = ['success' => true, 'message' => __('Sub Category is updated Successfully!')];
            }else{
                $data['unique_code'] = uniqid().date('').time();
                $category = KnbSubCategory::create($data);
                $response = ['success' => true, 'message' => __('Sub Category created Successfully!')];
            }
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("storeUpdateSubCategory", $e->getMessage());
        }
        return $response;
    }

    public function deleteSubCategory($unique_code)
    {
        try{
            $sub_category = KnbSubCategory::where('unique_code', $unique_code)->first();
            $article = KnbArticle::where('sub_category_id', $sub_category->id)->first();
            if(isset($article))
            {
                $response = ['success' => false, 'message' => __('Remove Articles under this subcategory to delete this!')];
            }else{
                if(isset($sub_category))
                {
                    $sub_category->delete();
                    $response = ['success' => true, 'message' => __('Sub Category is deleted Successfully!')];  
                }else{
                    $response = ['success' => false, 'message' => __('Sub Category is not found')];
                }
                
            }
            
            
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("deleteSubCategory", $e->getMessage());
        }
        return $response;
    }

    public function getSubCategoryByCategoryID($request)
    {
        try{
            $sub_category = KnbSubCategory::where('category_id', $request->category_id)->get();
            
            $response = ['success' => true, 'message' => __('Sub Category List of Category!'), 'data'=>$sub_category];
            
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("getSubCategoryByCategoryID", $e->getMessage());
        }
        return $response;
    }
}