<?php

namespace Modules\KnowledgeBase\Http\Services;

use Modules\KnowledgeBase\Http\Repositories\SubCategoryRepository;

class SubCategoryService
{
    private $subCategoryRepository;
    public function __construct()
    {
        $this->subCategoryRepository = new SubCategoryRepository;
    }
    public function getSubCategoryList()
    {
        $response = $this->subCategoryRepository->getSubCategoryList();
        return $response;
    }

    public function saveSubCategory($request)
    {
        $data = [
            'name' => $request->name,
            'category_id' => $request->category_id,
            'icon_class' => $request->icon_class,
            'status' => $request->status,
        ];

        $unique_code = isset($request->unique_code) ? $request->unique_code : null;

        $response = $this->subCategoryRepository->storeUpdateSubCategory($unique_code, $data);
        return $response;
    }

    public function getSubCategoryDetails($unique_code)
    {
        $response = $this->subCategoryRepository->getSubCategoryDetails($unique_code);
        return $response;
    }

    public function deleteSubCategory($unique_code)
    {
        $response = $this->subCategoryRepository->deleteSubCategory($unique_code);
        return $response;
    }

    public function setSubCategory($request)
    {
        $response = $this->subCategoryRepository->getSubCategoryByCategoryID($request);
        if($response['success'] == true)
        {
            $subCategoryList = $response['data'];
            
            $sub_category_options = '<option value=""  selected disabled > Choose Category</option>';
            foreach($subCategoryList as $item)
            {
                if($item->id == $request->sub_category_id)
                {
                    $sub_category_options.=  '<option value="'. $item->id .'" selected> '. $item->name .'</option>';
                }else{
                    $sub_category_options.=  '<option value="'. $item->id .'"> '. $item->name .'</option>';
                }
            }

            $setSubCategoryResponse = ['success' => true, 'message' => __('Sub Category append list'), 'data' => $sub_category_options];
            return $setSubCategoryResponse;

        }
        return $response;
    }

    
}