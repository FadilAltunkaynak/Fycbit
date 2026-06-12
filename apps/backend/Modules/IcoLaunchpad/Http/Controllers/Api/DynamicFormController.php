<?php

namespace Modules\IcoLaunchpad\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\IcoLaunchpad\Entities\Form;
use Modules\IcoLaunchpad\Entities\SubmitFormLists;
use Modules\IcoLaunchpad\Entities\SubmitFormDetails;
use Illuminate\Support\Facades\Validator;
use Modules\IcoLaunchpad\Http\Services\DynamicFormService;

class DynamicFormController extends Controller
{
    private $dynamicFormService;
    public function __construct()
    {
        $this->dynamicFormService = new DynamicFormService();
    }

    public function index()
    {
        $data['dynamic_form_for_ico_title'] = settings('dynamic_form_for_ico_title');
        $data['dynamic_form_for_ico_description'] = settings('dynamic_form_for_ico_description');
        $data['dynamic_form'] = Form::get();
        $data['dynamic_form']->map(function ($query) {
            if (isset($query->optionList)) {
                $query->optionList = json_decode($query->optionList);
            }
            return $query;
        });


        $response = ['success' => true, 'message' => __('Dynamic Form'), 'data' => $data];
        return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */

    public function submitDynamicForm(Request $request)
    {
        try {
            $dynamicFormService = new DynamicFormService();
            $response = $dynamicFormService->saveDynamicForm($request);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => __("Something went wrong")]);
        }
    }

    public function submittedDynamicFormList(Request $request)
    {
        $response = $this->dynamicFormService->getSubmittedFromListForUser($request->per_page);

        return response()->json($response);
    }
}
