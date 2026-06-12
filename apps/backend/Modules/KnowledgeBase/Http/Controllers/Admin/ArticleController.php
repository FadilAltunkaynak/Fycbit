<?php

namespace Modules\KnowledgeBase\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\KnowledgeBase\Http\Services\CategoryService;
use Modules\KnowledgeBase\Http\Services\SubCategoryService;
use Modules\KnowledgeBase\Http\Services\ArticleService;
use Modules\KnowledgeBase\Http\Requests\ArticleRequest;

class ArticleController extends Controller
{
    private $categoryService;
    private $subCategoryService;
    private $articleService;
    public function __construct()
    {
        $this->categoryService = new CategoryService;
        $this->subCategoryService = new SubCategoryService;
        $this->articleService = new ArticleService;
    }
    public function list()
    {
        $data['title'] = __('Article List');
        $article_response = $this->articleService->getArticleList();
        if($article_response['success'] == true)
        {
            $data['article_list'] = $article_response['data'];
        }
        return view('knowledgebase::admin.article.list', $data);
    }

    public function add()
    {
        $data['title'] = __('Add New Article');
        $category_response = $this->categoryService->getCategoryList();
        if($category_response['success'] == true)
        {
            $data['category_list'] = $category_response['data'];
        }

        return view('knowledgebase::admin.article.addEdit', $data);
    }

    public function setSubCategory(Request $request)
    {
        $response = $this->subCategoryService->setSubCategory($request);
        return response()->json($response);
    }

    public function save(ArticleRequest $request)
    {
        $response = $this->articleService->saveArticle($request);
        
        if($response['success'] ==true)
        {
            return redirect()->route('knowledgebase_articleList')->with(['success'=>$response['message']]);
        }else{
            return redirect()->route('knowledgebase_articleList')->with(['dismiss'=>$response['message']]);
        }
    }

    public function edit($unique_code)
    {
        $data['title'] = __('Update Article');

        $category_response = $this->categoryService->getCategoryList();
        if($category_response['success'] == true)
        {
            $data['category_list'] = $category_response['data'];
        }

        $article_response = $this->articleService->getArticleDetails($unique_code);
        if($article_response['success'] == true)
        {
            $data['item'] = $article_response['data'];
        }

        return view('knowledgebase::admin.article.addEdit', $data);
    }

    public function delete($unique_code)
    {
        $response = $this->articleService->deleteArticle($unique_code);
        if($response['success'] == true)
        {
            return back()->with(['success' => $response['message']]);
        }else{
            return back()->with(['dismiss' => $response['message']]);
        }
    }
}
