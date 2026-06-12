<?php
namespace Modules\KnowledgeBase\Http\Repositories\Support;

use Modules\KnowledgeBase\Entities\KnbCategory;
use Modules\KnowledgeBase\Entities\SupportTicket;
use Modules\KnowledgeBase\Entities\SupportProject;
use Modules\KnowledgeBase\Entities\SupportCategory;

class ProjectRepository{
    public function getProjectList()
    {
        $project_list = SupportProject::latest()->get();
        $response = ['success' => true, 'message' => __('Support Ticket Project List'), 'data' => $project_list];
        return $response;
    }
    public function getActiveProjectList()
    {
        $data['project_list'] = SupportProject::where('status', STATUS_ACTIVE)->latest()->get();
        $data['category'] = SupportCategory::whereStatus(STATUS_ACTIVE)->get();

        $data['project_list']->map(function($q){
            $q->label = $q->name;
            $q->value = $q->id;
        });

        $data['category']->map(function($q){
            $q->label = $q->name;
            $q->value = $q->id;
        });

        $response = ['success' => true, 'message' => __('Support Ticket Active Project List'), 'data' => $data];
        return $response;
    }
    public function storeUpdateProject($unique_code, $data)
    {
        try{
            if($unique_code !=null)
            {
                $project = SupportProject::where('unique_code', $unique_code)->first();
                $project->update($data);
                $response = ['success' => true, 'message' => __('Project is updated Successfully!')];
            }else{
                $data['unique_code'] = uniqid().date('').time();
                $project = SupportProject::create($data);
                $response = ['success' => true, 'message' => __('Project created Successfully!')];
            }
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("storeUpdateProject", $e->getMessage());
        }
        return $response;
    }

    public function getProjectDetails($unique_code)
    {
        $project_details = SupportProject::where('unique_code', $unique_code)->first();
        $response = ['success' => true, 'message' => __('Support Ticket Project Details'), 'data' => $project_details];
        return $response;
    }

    public function deleteProject($unique_code)
    {
        try{
            $project = SupportProject::where('unique_code', $unique_code)->first();
            $ticket = SupportTicket::where('project_id', $project->id)->first();
            if(isset($ticket))
            {
                $response = ['success' => false, 'message' => __('Please remove ticket under this project to delete this!')];
            }else{
                $project->delete();
                $response = ['success' => true, 'message' => __('Support Ticket Project is deleted Successfully!')];
            }
            
            
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("deleteProject", $e->getMessage());
        }
        return $response;
    }
}