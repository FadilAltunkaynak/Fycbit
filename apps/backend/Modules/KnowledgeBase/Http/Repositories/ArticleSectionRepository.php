<?php

namespace Modules\KnowledgeBase\Http\Repositories;

use Modules\KnowledgeBase\Entities\KnbArticleSection;
class ArticleSectionRepository{

    public function storeUpdateArticleSection($unique_code, $data)
    {
        try{
            if($unique_code !=null)
            {
                $category = KnbArticleSection::where('unique_code', $unique_code)->first();
                $category->update($data);
                $response = ['success' => true, 'message' => __('Article section is updated Successfully!')];
            }else{
                $data['unique_code'] = uniqid().date('').time();
                $category = KnbArticleSection::create($data);
                $response = ['success' => true, 'message' => __('Article section is created Successfully!')];
            }
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("storeUpdateArticleSection", $e->getMessage());
        }
        return $response;
    }

    public function getArticleSectionListByArticle($article_id)
    {
        try{
            $article_section_list = KnbArticleSection::where('article_id', $article_id)->get();
            $response = ['success' => true, 'message' => __('Article Section List'), 'data' => $article_section_list];
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("getArticleSectionListByArticle", $e->getMessage());
        }
        return $response;
    }

    public function getArticleSectionDetails($unique_code)
    {
        try{
            $article_section_details = KnbArticleSection::where('unique_code', $unique_code)->first();
            $response = ['success' => true, 'message' => __('Article Section Details'), 'data' => $article_section_details];
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("getArticleSectionListByArticle", $e->getMessage());
        }
        return $response;
    }

    public function deleteArticleSection($unique_code)
    {
        try{
            $article_section_details = KnbArticleSection::where('unique_code', $unique_code)->first();
            if(isset($article_section_details))
            {
                $article_section_details->delete();
                $response = ['success' => true, 'message' => __('Article Section is deleted successfully!')];
            }else{
                $response = ['success' => false, 'message' => __('Article Section is not found')];
            }
            
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("getArticleSectionListByArticle", $e->getMessage());
        }
        return $response;
    }
}