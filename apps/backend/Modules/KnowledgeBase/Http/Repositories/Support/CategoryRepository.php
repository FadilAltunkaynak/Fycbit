<?php
namespace Modules\KnowledgeBase\Http\Repositories\Support;

use Modules\KnowledgeBase\Entities\SupportCategory;
use Modules\KnowledgeBase\Entities\SupportTicket;

class CategoryRepository{

    public function getCategoryList()
    {
        $category_list = SupportCategory::latest()->get();
        $response = ['success' => true, 'message' => __('Support Ticket Category List'), 'data' => $category_list];
        return $response;
    }
    public function storeUpdateCategory($unique_code, $data)
    {
        try{
            if($unique_code !=null)
            {
                $category = SupportCategory::where('unique_code', $unique_code)->first();
                $category->update($data);
                $response = ['success' => true, 'message' => __('Category is updated Successfully!')];
            }else{
                $data['unique_code'] = uniqid().date('').time();
                $category = SupportCategory::create($data);
                $response = ['success' => true, 'message' => __('Category created Successfully!')];
            }
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("storeUpdateCategory", $e->getMessage());
        }
        return $response;
    }

    public function getCategoryDetails($unique_code)
    {
        $category_details = SupportCategory::where('unique_code', $unique_code)->first();
        $response = ['success' => true, 'message' => __('Support Ticket Category Details'), 'data' => $category_details];
        return $response;
    }

    public function deleteCategory($unique_code)
    {
        try{
            $category = SupportCategory::where('unique_code', $unique_code)->first();
            $ticket = SupportTicket::where('category_id', $category->id)->first();
            if(isset($ticket))
            {
                $response = ['success' => false, 'message' => __('Please remove ticket under this category to delete this!')];
            }else{
                $category->delete();
                $response = ['success' => true, 'message' => __('Support Ticket Category is deleted Successfully!')];
            }
            
            
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("deleteCategory", $e->getMessage());
        }
        return $response;
    }

    public function getActiveCategoryList()
    {
        $category_list = SupportCategory::where('status', STATUS_ACTIVE)->latest()->get();
        $response = ['success' => true, 'message' => __('Active Category List'), 'data' => $category_list];
        return $response;
    }
}