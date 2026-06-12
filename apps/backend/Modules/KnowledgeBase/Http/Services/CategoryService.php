<?php

namespace Modules\KnowledgeBase\Http\Services;

use Modules\KnowledgeBase\Http\Repositories\CategoryRepository;

use function Amp\call;

class CategoryService {

    private $categoryRepository;
    public function __construct()
    {
        $this->categoryRepository = new CategoryRepository;
    }
    public function getCategoryList()
    {
        $response = $this->categoryRepository->getCategoryList();
        return $response;
    }

    public function getCategoryListWithAllDetails($article_list_limit = null)
    {
        $response = $this->categoryRepository->getCategoryListWithAllDetails($article_list_limit);
        return $response;
    }
    public function getCategoryDetails($unique_code)
    {
        $response = $this->categoryRepository->getCategoryDetails($unique_code);
        return $response;
    }
    public function saveCategory($request)
    {
        $data = [
            'name' => $request->name,
            'icon_class' => $request->icon_class,
            'description' => $request->description,
            'status' => $request->status,
        ];

        $unique_code = isset($request->unique_code) ? $request->unique_code : null;

        $response = $this->categoryRepository->storeUpdateCategory($unique_code, $data);
        return $response;
    }

    public function deleteCategory($unique_code)
    {
        $response = $this->categoryRepository->deleteCategory($unique_code);
        return $response;
    }

    // get category
    public function getCategory($request,$id)
    {
        $response = responseData(false);
        try {
            $limit = $request->limit ?? 20;
            $data = $this->categoryRepository->getAllSubCategory($limit,$id);
            $response = responseData(true,__('Data get successfully'),$data);
        } catch(\Exception $e) {
            storeException('getCategory',$e->getMessage());
        }
        return $response;
    }
}
