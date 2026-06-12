<?php

namespace Modules\KnowledgeBase\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\KnowledgeBase\Http\Services\CategoryService;
use Modules\KnowledgeBase\Http\Services\ArticleService;

class KnowledgeBaseController extends Controller
{
    private $categoryService;
    private $articleService;
    public function __construct()
    {
        $this->categoryService = new CategoryService;
        $this->articleService = new ArticleService;
    }

    public function index(Request $request)
    {
        $response = $this->categoryService->getCategoryListWithAllDetails($request->article_list_limit);
        
        return response()->json($response);
    }
    public function articleListByCategory(Request $request)
    {
        $response = $this->articleService->getArticleListForCategory($request->category_unique_code, $request->article_list_limit);
        
        return $response;
    }
    public function articleListBySubcategory(Request $request)
    {
        $response = $this->articleService->getArticleListForSubcategory($request->subcategory_unique_code);
        
        return $response;
    }
    public function articleDetails(Request $request)
    {
        if(isset($request->unique_code))
        {
            $response = $this->articleService->getArticleDetails($request->unique_code);
            if($response['success'] == true)
            {
                $data['article_details'] = $response['data'];
                $related_article_response = $this->articleService->getRelatedArticle($response['data']);
                if($related_article_response['success'])
                {
                    $data['related_article_list'] = $related_article_response['data'];
                }
            }
            $response = ['success' => true, 'message' => __('Article details with related article'),'data'=>$data];
        }else{
            $response = ['success' => false, 'message' => __('Article unique_code is required')];
        }
        return response()->json($response);
    }

    public function articleSearch(Request $request)
    {
        $response = $this->articleService->searchArticle($request);
        
        return response()->json($response);
    }
}
