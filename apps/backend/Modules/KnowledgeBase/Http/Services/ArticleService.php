<?php

namespace Modules\KnowledgeBase\Http\Services;

use Modules\KnowledgeBase\Http\Repositories\ArticleRepository;

class ArticleService {

    private $articleRepository;

    public function __construct()
    {
        $this->articleRepository = new ArticleRepository;
    }

    public function getArticleList()
    {
        $response = $this->articleRepository->getArticleList();
        return $response;
    }
    public function getArticleListForCategory($category_unique_code, $article_list_limit = null)
    {
        $response = $this->articleRepository->getArticleListForCategory($category_unique_code, $article_list_limit);
        return $response;
    }
    public function getArticleListForSubcategory($subcategory_unique_code)
    {
        $response = $this->articleRepository->getArticleListForSubcategory($subcategory_unique_code);
        return $response;
    }

    public function getRelatedArticle($article_details)
    {
        $response = $this->articleRepository->getRelatedArticle($article_details);
        return $response;
    }

    public function getArticleDetails($unique_code)
    {
        $response = $this->articleRepository->getArticleDetails($unique_code);
        return $response;
    }
    public function saveArticle($request)
    {
        try{
            $data = [
                'category_id'=> $request->category_id,
                'sub_category_id'=> $request->sub_category_id,
                'title'=> $request->title,
                'description' => $request->body,
                'status' => $request->status,
                'icon_class'=>$request->icon_class
            ];

            $unique_code = isset($request->unique_code) ? $request->unique_code : null;
            $oldImage = null;

            if ($request->image) {
                $imageName = uploadAnyFileSupport($request->image, FILE_KNOWLEDGE_BASE_STORAGE_PATH, $oldImage);
                $data['feature_image'] = $imageName;
            }
            $response = $this->articleRepository->storeUpdateArticle($unique_code, $data);


        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("saveArticle", $e->getMessage());
        }
        return $response;
    }

    public function deleteArticle($unique_code)
    {
        $response = $this->articleRepository->deleteArticle($unique_code);
        return $response;
    }

    public function searchArticle($request)
    {
        $response = $this->articleRepository->searchArticle($request);
        return $response;
    }
    
}
