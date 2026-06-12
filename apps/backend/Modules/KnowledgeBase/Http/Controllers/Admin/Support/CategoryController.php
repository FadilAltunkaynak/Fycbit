<?php

namespace Modules\KnowledgeBase\Http\Controllers\Admin\Support;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\KnowledgeBase\Http\Services\Support\CategoryService;
use Modules\KnowledgeBase\Http\Requests\SupportCategoryRequest;
class CategoryController extends Controller
{
    private $categoryService;
    public function __construct()
    {
        $this->categoryService = new CategoryService;
    }
    public function list()
    {
        $data['title'] = __('Support category List');
        $response = $this->categoryService->getCategoryList();
        if($response['success'])
        {
            $data['category_list'] = $response['data'];
        }
        return view('knowledgebase::admin.support.category.list',$data);
    }

    public function add()
    {
        $data['title'] = __('Add New Category for Support Ticket');
        return view('knowledgebase::admin.support.category.addEdit', $data);
    }

    public function save(SupportCategoryRequest $request)
    {
        $response = $this->categoryService->saveCategory($request);
        if($response['success'] == true)
        {
            return redirect()->route('support_category_list')->with(['success' => $response['message']]);
        }else{
            return back()->with(['dismiss' => $response['message']]);
        }
    }

    public function edit($unique_code)
    {
        $data['title'] = __('Update Category for Support Ticket');
        $response = $this->categoryService->getCategoryDetails($unique_code);

        if($response['success'])
        {
            $data['item'] = $response['data'];
        }

        return view('knowledgebase::admin.support.category.addEdit', $data);

    }

    public function delete($unique_code)
    {
        $response = $this->categoryService->deleteCategory($unique_code);
        if($response['success'])
        {
            return back()->with(['success' => $response['message']]);
            
        }else{
            return back()->with(['dismiss' => $response['message']]);
        }
    }
}
