<?php

namespace Modules\KnowledgeBase\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\KnowledgeBase\Http\Services\CategoryService;
use Modules\KnowledgeBase\Http\Services\SubCategoryService;
use Modules\KnowledgeBase\Http\Requests\SubCategoryRequest;

class SubCategoryController extends Controller
{
    private $categoryService;
    private $subCategoryService;
    public function __construct()
    {
        $this->categoryService = new CategoryService;
        $this->subCategoryService = new SubCategoryService;
    }

    public function list()
    {
        $data['title'] = __('Sub Category List');
        $sub_category_list_response =  $this->subCategoryService->getSubCategoryList();
        if($sub_category_list_response['success'] == true)
        {
            $data['sub_category_list'] = $sub_category_list_response['data'];
        }
        
        return view('knowledgebase::admin.sub-category.list', $data);
    }

    public function add()
    {
        $data['title'] = __('Add New Sub Category');
        $category_list_response =  $this->categoryService->getCategoryList();
        if($category_list_response['success'] == true)
        {
            $data['category_list'] = $category_list_response['data'];
        }
        return view('knowledgebase::admin.sub-category.addEdit', $data);
    }

    public function save(SubCategoryRequest $request)
    {
        $response = $this->subCategoryService->saveSubCategory($request);
        if($response['success'] == true)
        {
            return redirect()->route('knowledgebase_subCategoryList')->with(['success' => $response['message']]);
        }else{
            return back()->with(['dismiss' => $response['message']]);
        }
    }

    public function edit($unique_code)
    {
        $data['title'] = __('Update Sub Category');

        $category_list_response =  $this->categoryService->getCategoryList();
        if($category_list_response['success'] == true)
        {
            $data['category_list'] = $category_list_response['data'];
        }

        $sub_category_response = $this->subCategoryService->getSubCategoryDetails($unique_code);
        if($sub_category_response['success'] == true)
        {
            $data['item'] = $sub_category_response['data'];
        }
        
        return view('knowledgebase::admin.sub-category.addEdit', $data);
    }

    public function deleteSubCategory($unique_code)
    {
        $response = $this->subCategoryService->deleteSubCategory($unique_code);
        if($response['success'] == true)
        {
            return back()->with(['success' => $response['message']]);
        }else{
            return back()->with(['dismiss' => $response['message']]);
        }
    }
}
