<?php

namespace Modules\KnowledgeBase\Http\Controllers\Admin;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\KnowledgeBase\Http\Requests\CategoryRequest;
use Modules\KnowledgeBase\Http\Services\CategoryService;

class CategoryController extends Controller
{
    private $categoryService;

    public function __construct()
    {
        $this->categoryService = new CategoryService;
    }
    public function list()
    {
        $data['title'] = __('Category List');
        $category_list_response =  $this->categoryService->getCategoryList();
        if($category_list_response['success'] == true)
        {
            $data['category_list'] = $category_list_response['data'];
        }
        
        return view('knowledgebase::admin.category.list', $data);
    }

    public function add()
    {
        $data['title'] = __('Add New Category');
        return view('knowledgebase::admin.category.addEdit', $data);
    }

    public function save(CategoryRequest $request)
    {
        $response = $this->categoryService->saveCategory($request);
        if($response['success'] == true)
        {
            return redirect()->route('knowledgebase_categoryList')->with(['success' => $response['message']]);
        }else{
            return back()->with(['dismiss' => $response['message']]);
        }
    }

    public function edit($unique_code)
    {
        $data['title'] = __('Update Category');
        $category_details_response = $this->categoryService->getCategoryDetails($unique_code);
        if($category_details_response['success'] == true)
        {
            $data['item'] = $category_details_response['data'];
        }
        
        return view('knowledgebase::admin.category.addEdit', $data);
    }

    public function delete($unique_code)
    {
        $response = $this->categoryService->deleteCategory($unique_code);
        if($response['success'] == true)
        {
            return back()->with(['success' => $response['message']]);
        }else{
            return back()->with(['dismiss' => $response['message']]);
        }
    }
}
