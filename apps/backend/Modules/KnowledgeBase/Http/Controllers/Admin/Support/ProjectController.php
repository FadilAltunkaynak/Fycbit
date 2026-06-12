<?php

namespace Modules\KnowledgeBase\Http\Controllers\Admin\Support;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\KnowledgeBase\Http\Services\Support\ProjectService;
use Modules\KnowledgeBase\Http\Requests\SupportProjectRequest;

class ProjectController extends Controller
{
    private $projectService;
    public function __construct()
    {
        $this->projectService = new ProjectService;
    }
    public function list()
    {
        $data['title'] = __('Support project List');
        $response = $this->projectService->getProjectList();
        if($response['success'])
        {
            $data['project_list'] = $response['data'];
        }
        return view('knowledgebase::admin.support.project.list',$data);
    }

    public function add()
    {
        $data['title'] = __('Add New project for Support Ticket');
        return view('knowledgebase::admin.support.project.addEdit', $data);
    }

    public function save(SupportProjectRequest $request)
    {
        $response = $this->projectService->saveProject($request);
        if($response['success'] == true)
        {
            return redirect()->route('support_project_list')->with(['success' => $response['message']]);
        }else{
            return back()->with(['dismiss' => $response['message']]);
        }
    }

    public function edit($unique_code)
    {
        $data['title'] = __('Update Project for Support Ticket');
        $response = $this->projectService->getProjectDetails($unique_code);

        if($response['success'])
        {
            $data['item'] = $response['data'];
        }

        return view('knowledgebase::admin.support.project.addEdit', $data);

    }

    public function delete($unique_code)
    {
        $response = $this->projectService->deleteProject($unique_code);
        if($response['success'])
        {
            return back()->with(['success' => $response['message']]);
            
        }else{
            return back()->with(['dismiss' => $response['message']]);
        }
    }
}
