<?php
namespace Modules\KnowledgeBase\Http\Services\Support;

use Modules\KnowledgeBase\Http\Repositories\Support\CategoryRepository;
class CategoryService{

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
    public function saveCategory($request)
    {
        $data = [
            'name' => $request->name,
            'status' => $request->status,
        ];

        $unique_code = isset($request->unique_code) ? $request->unique_code : null;

        $response = $this->categoryRepository->storeUpdateCategory($unique_code, $data);
        return $response;
    }

    public function getCategoryDetails($unique_code)
    {
        $response = $this->categoryRepository->getCategoryDetails($unique_code);
        return $response;
    }

    public function deleteCategory($unique_code)
    {
        $response = $this->categoryRepository->deleteCategory($unique_code);
        return $response;
    }

    public function getActiveCategoryList()
    {
        $response = $this->categoryRepository->getActiveCategoryList();
        return $response;
    }
}