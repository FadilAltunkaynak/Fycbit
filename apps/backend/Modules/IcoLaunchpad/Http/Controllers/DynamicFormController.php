<?php

namespace Modules\IcoLaunchpad\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\IcoLaunchpad\Entities\Form;
use Modules\IcoLaunchpad\Entities\SubmitFormLists;
use Modules\IcoLaunchpad\Entities\SubmitFormDetails;
use App\Http\Services\MyCommonService;
use Illuminate\Support\Facades\Log;
use App\Jobs\MailSend;
use App\Http\Repositories\SettingRepository;

class DynamicFormController extends Controller
{
    private $settingRepo;
    public function __construct()
    {
        $this->settingRepo = new SettingRepository();
    }
    public function index()
    {
        $data['title'] = __('Dynamic form create for ICO');
        $data['formData'] = Form::get();
        return view('icolaunchpad::index', $data);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        // return $request;
        if (isset($request->option)) {
            Form::truncate();
            foreach ($request->option as $key => $option) {
                $data = [];
                $data['title'] = $option['title'];
                $data['type'] = $option['type'];
                $data['required'] = $option['required'];
                if (isset($option['optionList'])) {
                    $data['is_option'] = 1;
                    $optionListArray = [];
                    foreach ($option['optionList'] as $option_list_item) {
                        if (isset($option_list_item)) {
                            array_push($optionListArray, $option_list_item);
                        }
                    }
                    $data['optionList'] = json_encode($optionListArray);
                }
                if (isset($option['file_type'])) {
                    $data['is_file'] = 1;
                    $data['file_type'] = $option['file_type'];
                    $data['file_link'] = $option['file_link'];
                }
                Form::create($data);
            }
        }
        return back()->with(['success' => __('Updated form successfully')]);
    }

    public function submittedFormICO()
    {
        $data['title'] = __('Submitted Form for ICO');
        $data['submitted_form_list'] = SubmitFormLists::with(['user'])->latest()->get();
        return view('icolaunchpad::submitted-form-list', $data);
    }

    public function submittedFormDetails($form_id)
    {
        $data['title'] = __('Submitted Form details for ICO');
        $data['submitted_form'] = SubmitFormLists::with(['formDetails'])->find(decrypt($form_id));
        return view('icolaunchpad::submitted-form-details', $data);
    }

    public function accpetedSubmittedFormICO(Request $request)
    {
        try {
            $submitted_form = SubmitFormLists::with(['user'])->where('unique_id', $request->unique_id)->first();
            if (isset($submitted_form->user)) {
                $user_id = $submitted_form->user->id;
                $title = __('Your submitted form for ICO is accepted');
                $message = $request->message;
                $mycommonService = new MyCommonService;
                $mycommonService->sendNotificationToUserUsingSocket($user_id, $title, $message);

                $data['mailTemplate'] = 'email.ico_form_accept_reject';
                $data['name'] = $submitted_form->user->first_name . ' ' . $submitted_form->user->last_name;
                $data['subject'] = $title;
                $data['details'] = $message;
                $data['to'] = $submitted_form->user->email;

                dispatch(new MailSend($data));

                $submitted_form->status = STATUS_ACCEPTED;
                $submitted_form->save();

                return back()->with(['success' => __('Submitted form accepted successfully!')]);
            }
            return back()->with(['dismiss' => __('User not found!')]);
        } catch (\Exception $e) {
            storeException("accpetedSubmittedFormICO", $e->getMessage());
        }
        return back()->with(['dismiss' => __('Something went wrong!')]);
    }

    public function rejectedSubmittedFormICO(Request $request)
    {
        try {
            $submitted_form = SubmitFormLists::with(['user'])->where('unique_id', $request->unique_id)->first();
            if (isset($submitted_form->user)) {
                $user_id = $submitted_form->user->id;
                $title = __('Your submitted form for ICO is rejected');
                $message = $request->message;
                $mycommonService = new MyCommonService;
                $mycommonService->sendNotificationToUserUsingSocket($user_id, $title, $message);

                $data['mailTemplate'] = 'email.ico_form_accept_reject';
                $data['name'] = $submitted_form->user->first_name . ' ' . $submitted_form->user->last_name;
                $data['subject'] = $title;
                $data['details'] = $message;
                $data['to'] = $submitted_form->user->email;

                dispatch(new MailSend($data));

                $submitted_form->status = STATUS_REJECTED;
                $submitted_form->save();

                return back()->with(['success' => __('Submitted form rejected successfully!')]);
            }
            return back()->with(['dismiss' => __('User not found!')]);
        } catch (\Exception $e) {
            storeException("accpetedSubmittedFormICO", $e->getMessage());
        }
        return back()->with(['dismiss' => __('Something went wrong!')]);
    }

    public function setupDynamicForm()
    {
        $data['title'] = __('Setup Dynamic Form');

        return view('icolaunchpad::setup-dynamic-form', $data);
    }

    public function setupDynamicFormSave(Request $request)
    {
        $response = $this->settingRepo->saveAdminSetting($request);

        return back()->with(['success' => $response['message']]);
    }
}
