<?php

namespace Modules\KnowledgeBase\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\KnowledgeBase\Http\Requests\ArticleSectionRequest;
use Modules\KnowledgeBase\Http\Services\ArticleSectionService;
use Modules\KnowledgeBase\Http\Services\ArticleService;
class ArticleSectionController extends Controller
{
    private $articleService;
    private $articleSectionService;
    
    public function __construct()
    {
        $this->articleService = new ArticleService;
        $this->articleSectionService = new ArticleSectionService;
    }

    public function add($article_unique_code)
    {
        $article_response = $this->articleService->getArticleDetails($article_unique_code);
        if($article_response['success'] == true && isset($article_response['data']))
        {   
            $title = $article_response['data']['title'];
            $data['title'] = __('Add New Section For "').$title.__('" Article');
            $data['article'] = $article_response['data'];

            return view('knowledgebase::admin.article.section.addEdit', $data);
        }else{
            $message = __('Article not found');
            return back()->with(['dismiss' => $message]);
        }
    }

    public function save(ArticleSectionRequest $request)
    {
        $response = $this->articleSectionService->storeUpdateArticleSection($request);
        
        if($response['success'] ==true)
        {
            return redirect()->route('knowledgebase_articleSectionList',$request->article_unique_code)->with(['success'=>$response['message']]);
        }else{
            return redirect()->route('knowledgebase_articleList')->with(['dismiss'=>$response['message']]);
        }
    }

    public function list($article_unique_code)
    {
        $article_response = $this->articleService->getArticleDetails($article_unique_code);
        if($article_response['success'] == true && isset($article_response['data']))
        {   
            $article_details = $article_response['data'];
            $title = $article_details['title'];
            $article_id = $article_details->id;
            $data['article_details'] = $article_details;
            $data['title'] = __('Section List For "').$title.__('" Article');
            $article_section_response = $this->articleSectionService->getArticleSectionListByArticle($article_id);
            if($article_section_response['success'] == true)
            {
                $data['article_section_list'] = $article_section_response['data'];
            }

            return view('knowledgebase::admin.article.section.list', $data);
        }else{
            $message = __('Article not found');
            return back()->with(['dismiss' => $message]);
        }
    }

    public function edit($article_unique_code, $section_unique_code)
    {
        $article_response = $this->articleService->getArticleDetails($article_unique_code);
        if($article_response['success'] == true && isset($article_response['data']))
        {   
            $title = $article_response['data']['title'];
            $data['title'] = __('Add New Section For "').$title.__('" Article');
            $data['article'] = $article_response['data'];
            $section_response = $this->articleSectionService->getArticleSectionDetails($section_unique_code);
            if($section_response['success'] == true)
            {
                $data['item'] = $section_response['data'];
                return view('knowledgebase::admin.article.section.addEdit', $data);
            }else{
                return back()->with(['dismiss'=> $section_response['message']]);
            }
        }else{
            $message = __('Article not found');
            return back()->with(['dismiss' => $message]);
        }
    }

    public function delete($article_unique_code, $section_unique_code)
    {
        $article_response = $this->articleService->getArticleDetails($article_unique_code);
        if($article_response['success'] == true && isset($article_response['data']))
        {
            $response = $this->articleSectionService->deleteArticleSection($section_unique_code);
            if($response['success'] == true)
            {
                return redirect()->route('knowledgebase_articleSectionList',$article_unique_code)->with(['success'=>$response['message']]); 
            }else{
                return redirect()->route('knowledgebase_articleSectionList',$article_unique_code)->with(['dismiss'=>$response['message']]); 
            }
        }else{
            $message = __('Article not found');
            return back()->with(['dismiss' => $message]);
        }
    }
}
