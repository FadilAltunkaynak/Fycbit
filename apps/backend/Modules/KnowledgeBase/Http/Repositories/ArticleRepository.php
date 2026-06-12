<?php

namespace Modules\KnowledgeBase\Http\Repositories;

use Modules\KnowledgeBase\Entities\KnbArticle;
use Modules\KnowledgeBase\Entities\KnbArticleSection;
use Modules\KnowledgeBase\Entities\KnbCategory;
use Modules\KnowledgeBase\Entities\KnbSubCategory;

class ArticleRepository{

    public function getArticleList()
    {
        try{
            $article_list = KnbArticle::latest()->get();
            $response = ['success' => true, 'message' => __('Article list!'), 'data'=> $article_list];

        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("getArticleList", $e->getMessage());
        }
        return $response;
    }

    public function getArticleListForCategory($category_unique_code, $article_list_limit = null)
    {
        try{
            $category = KnbCategory::where('unique_code',$category_unique_code)->first();
            if(isset($category))
            {
                $article_list_limit = $article_list_limit ?? 4;
                $data['category_details'] = $category;
                $data['subcategory_list'] = KnbSubCategory::with(['knbArticles' => function ($query) use($article_list_limit) {
                                                        $query->where('status', STATUS_ACTIVE)->limit($article_list_limit);
                                                    }])->where('category_id', $category->id)->get();

                $response = ['success' => true, 'message' => __('Subcategory list under category!'), 'data'=> $data];
            }else{
                $response = ['success' => false, 'message' => __('Invalid request!')];
            }


        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("getArticleListForCategory", $e->getMessage());
        }
        return $response;
    }
    public function getArticleListForSubcategory($subcategory_unique_code)
    {
        try{
            $subcategory = KnbSubCategory::where('unique_code',$subcategory_unique_code)->first();
            if(isset($subcategory))
            {
                $data['sub_category_details'] = $subcategory;
                $data['sub_category_details']->main_category_title = $subcategory->category->name;
                $data['article_list'] = KnbArticle::where('sub_category_id', $subcategory->id)->get();
                $response = ['success' => true, 'message' => __('Article list!'), 'data'=> $data];
            }else{
                $response = ['success' => false, 'message' => __('Invalid request!')];
            }


        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("getArticleList", $e->getMessage());
        }
        return $response;
    }

    public function getArticleDetails($unique_code)
    {
        try{
            $article_details = KnbArticle::where('unique_code', $unique_code)->first();
            
            if(isset($article_details->feature_image))
            {
                $article_details['feature_image_url'] = supportCreateImageUrl(FILE_KNOWLEDGE_BASE_VIEW_PATH, $article_details->feature_image);
            }else{
                $article_details['feature_image_url'] = null;
            }
            
            $response = ['success' => true, 'message' => __('Article details!'), 'data'=> $article_details];

        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("getArticleDetails", $e->getMessage());
        }
        return $response;
    }
    public function storeUpdateArticle($unique_code, $data)
    {
        try{
            if($unique_code !=null)
            {
                $category = KnbArticle::where('unique_code', $unique_code)->first();
                $category->update($data);
                $response = ['success' => true, 'message' => __('Article is updated Successfully!')];
            }else{
                $data['unique_code'] = uniqid().date('').time();
                $category = KnbArticle::create($data);
                $response = ['success' => true, 'message' => __('Article created Successfully!')];
            }
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("storeUpdateArticle", $e->getMessage());
        }
        return $response;
    }

    public function deleteArticle($unique_code)
    {
        try{
            $article = KnbArticle::where('unique_code', $unique_code)->first();
            $sectionArticle = KnbArticleSection::where('article_id', $article->id)->first();
            if(isset($sectionArticle))
            {
                $response = ['success' => false, 'message' => __('Please remove subsection under this article to delete this!')];
            }else{
                if(isset($article))
                {
                    $article->delete();
                    $response = ['success' => true, 'message' => __('Article is deleted Successfully!')];
                }else{
                    $response = ['success' => false, 'message' => __('Article is not found')];
                }
                
            }

        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("deleteArticle", $e->getMessage());
        }
        return $response;
    }

    public function searchArticle($request)
    {
        $article_list = KnbArticle::with('knbArticleSections')->where('title', 'LIKE', "%{$request->search}%")
            ->orWhere('description', 'LIKE', "%{$request->search}%")
            ->orWhere(function($q) use($request){
                $q->whereHas('knbArticleSections', function ($query) use ($request) {
                    $query->where('title', 'LIKE', "%{$request->search}%")
                            ->orWhere('description', 'LIKE', "%{$request->search}%");
                                        });
            })->get();
        $response = ['success' => true, 'message' => __('Search result'), 'data' => $article_list];

        return $response;
    }

    public function getRelatedArticle($article_details)
    {
        $article_list = KnbArticle::where('id','<>',$article_details->id)->where('sub_category_id', $article_details->sub_category_id)->take(5)->get();
        $response = ['success' => true, 'message' => __('Related article list'), 'data' => $article_list];
        return $response;
    }
}
