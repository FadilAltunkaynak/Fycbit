<?php

namespace Modules\KnowledgeBase\Http\Repositories;

use Modules\KnowledgeBase\Entities\KnbCategory;
use Modules\KnowledgeBase\Entities\KnbSubCategory;
use PhpOffice\PhpSpreadsheet\Calculation\Category;

class CategoryRepository {

    public function getCategoryList()
    {
        try{
            $category_list = KnbCategory::latest()->get();
            $response = ['success' => true, 'message' => __('Category List'), 'data' => $category_list];
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("getCategoryList", $e->getMessage());
        }

        return $response;
    }

    public function getCategoryListWithAllDetails($article_list_limit = null)
    {
        $article_list_limit = $article_list_limit ?? 4;
        
        $category_list = KnbCategory::with([
            'knbSubCategory' => function ($query) use($article_list_limit) {
                $query->with(['knbArticles' => function ($query) use($article_list_limit) {
                    $query->where('status', STATUS_ACTIVE)->limit($article_list_limit);
                }])->where('status', STATUS_ACTIVE);
            }
            ])
            ->where('status', STATUS_ACTIVE)->get();

        $response = ['success' => true, 'message' => __('Category list with all details'), 'data' => $category_list];
        return $response;
    }
    public function getCategoryDetails($unique_code)
    {
        try{
            $category_details = KnbCategory::where('unique_code', $unique_code)->first();
            $response = ['success' => true, 'message' => __('Category Details'), 'data' => $category_details];
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("getCategoryDetails", $e->getMessage());
        }

        return $response;
    }
    public function storeUpdateCategory($unique_code,$data)
    {
        try{
            if($unique_code !=null)
            {
                $category = KnbCategory::where('unique_code', $unique_code)->first();
                $category->update($data);
                $response = ['success' => true, 'message' => __('Category is updated Successfully!')];
            }else{
                $data['unique_code'] = uniqid().date('').time();
                $category = KnbCategory::create($data);
                $response = ['success' => true, 'message' => __('Category created Successfully!')];
            }
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("storeUpdateCategory", $e->getMessage());
        }
        return $response;
    }

    public function deleteCategory($unique_code)
    {
        try{
            $category = KnbCategory::where('unique_code', $unique_code)->first();
            $subcategory = KnbSubCategory::where('category_id', $category->id)->first();
            if(isset($subcategory))
            {
                $response = ['success' => false, 'message' => __('Please remove sub category under this category to delete this!')];
            }else{

                if(isset($category))
                {
                    $category->delete();
                    $response = ['success' => true, 'message' => __('Category is deleted Successfully!')];
                }else{
                    $response = ['success' => false, 'message' => __('Category is not found')];
                }

            }

        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("deleteCategory", $e->getMessage());
        }
        return $response;
    }

    public function getAllSubCategory($limit,$id)
    {
        $data['category'] = KnbCategory::where(['unique_code' => $id, 'status' => STATUS_ACTIVE])->first();
        $data['items'] = KnbSubCategory::join('knb_categories','knb_categories.id','=','knb_sub_categories.category_id')
            ->where('knb_categories.unique_code',$id)
            ->where('knb_categories.status',STATUS_ACTIVE)
            ->where('knb_sub_categories.status',STATUS_ACTIVE)
            ->with(['knbArticles' => function ($query) use($limit) {
                    $query->where('knb_articles.status', STATUS_ACTIVE)
                    ->limit($limit);
                }])->select('knb_sub_categories.*')

            // ->paginate($limit);
            ->get();

        return $data;
    }
}
