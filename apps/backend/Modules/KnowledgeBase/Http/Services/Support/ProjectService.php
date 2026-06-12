<?php
namespace Modules\KnowledgeBase\Http\Services\Support;

use Modules\KnowledgeBase\Http\Repositories\Support\ProjectRepository;
class ProjectService{

    private $projectRepository;
    public function __construct()
    {
        $this->projectRepository = new ProjectRepository;
    }

    public function getProjectList()
    {
        $response = $this->projectRepository->getProjectList();
        return $response;
    }

    public function getActiveProjectList()
    {
        $response = $this->projectRepository->getActiveProjectList();
        return $response;
    }
    public function saveProject($request)
    {
        $data = [
            'name' => $request->name,
            'status' => $request->status,
        ];

        $unique_code = isset($request->unique_code) ? $request->unique_code : null;

        $response = $this->projectRepository->storeUpdateProject($unique_code, $data);
        return $response;
    }

    public function getProjectDetails($unique_code)
    {
        $response = $this->projectRepository->getProjectDetails($unique_code);
        return $response;
    }

    public function deleteProject($unique_code)
    {
        $response = $this->projectRepository->deleteProject($unique_code);
        return $response;
    }
}