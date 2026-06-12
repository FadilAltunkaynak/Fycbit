<?php

namespace Modules\KnowledgeBase\Http\Services;

use Modules\KnowledgeBase\Http\Repositories\ArticleSectionRepository;

class ArticleSectionService{

    private $articleSectionRepository;
    public function __construct()
    {
        $this->articleSectionRepository = new ArticleSectionRepository;
    }
    public function storeUpdateArticleSection($request)
    {
        $data = [
            'article_id'=> $request->article_id,
            'title'=> $request->title,
            'description'=> $request->body,
            'status'=> $request->status,
            'icon_class'=>$request->icon_class
        ];

        $unique_code = isset($request->unique_code) ? $request->unique_code : null;

        $response = $this->articleSectionRepository->storeUpdateArticleSection($unique_code, $data);
        return $response;
    }

    public function getArticleSectionListByArticle($article_id)
    {
        $response = $this->articleSectionRepository->getArticleSectionListByArticle($article_id);
        return $response;
    }

    public function getArticleSectionDetails($unique_code)
    {
        $response = $this->articleSectionRepository->getArticleSectionDetails($unique_code);
        return $response;
    }

    public function deleteArticleSection($unique_code)
    {
        $response = $this->articleSectionRepository->deleteArticleSection($unique_code);
        return $response;
    }
}