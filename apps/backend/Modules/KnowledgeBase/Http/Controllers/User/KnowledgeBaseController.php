<?php

namespace Modules\KnowledgeBase\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\KnowledgeBase\Http\Services\CategoryService;
use Modules\KnowledgeBase\Http\Services\ArticleService;
use Illuminate\Support\Str;

class KnowledgeBaseController extends Controller
{
    private $categoryService;
    private $articleService;
    public function __construct()
    {
        $this->categoryService = new CategoryService;
        $this->articleService = new ArticleService;
    }
    public function index()
    {
        $response = $this->categoryService->getCategoryListWithAllDetails();

        if($response['success'] == true)
        {
            $data['category_list'] = $response['data'];
        }
        return view('knowledgebase::user.dashboard',$data);
    }

    public function knowledgeCategory(Request $request,$id)
    {
        $response = $this->categoryService->getCategory($request,$id);
        // dd($response);
        if($response['success'] == true) {
            return view('knowledgebase::user.article.category.sub_category',$response['data']);
        } else {
            return redirect()->back()->with('dismiss',$response['message']);
        }
    }

    public function articleList($subcategory_unique_code)
    {
         $response = $this->articleService->getArticleListForSubcategory($subcategory_unique_code);
        // $data=$subcategory_unique_code;
        if($response['success'] == true) {
            return view('knowledgebase::user.article.list',$response['data']);
        } else {
            return redirect()->back()->with('dismiss',$response['message']);
        }
    }
    public function articleDetails($unique_code)
    {
        $response = $this->articleService->getArticleDetails($unique_code);
        if($response['success'] == true)
        {
            $data['article_details'] = $response['data'];
            $related_article_response = $this->articleService->getRelatedArticle($response['data']);
            if($related_article_response['success'])
            {
                $data['related_article_list'] = $related_article_response['data'];
            }
        }
        return view('knowledgebase::user.article.details',$data);
    }

    public function articleSearch(Request $request)
    {
        $data['search'] = $request->search;
        $response = $this->articleService->searchArticle($request);
        if($response['success'] ==true)
        {
            $data['article_list'] = $response['data'];
        }
        return view('knowledgebase::user.article.search', $data);
    }

    public function articleSearchSuggestion(Request $request)
    {
        $data['success'] = false;
        if(isset($request->search))
        {
            $response = $this->articleService->searchArticle($request);
            if($response['success'] == true)
            {
                $data['success'] = true;
                $article_list = $response['data'];

                $data['view'] = view('knowledgebase::user.article.partial.search-result-suggest', compact('article_list'))->render();
            }
        }else{
            $data['view'] = '<a href="#">' . __('Write something to search here') . '</a>';
        }

        return response()->json($data);


    }
}
