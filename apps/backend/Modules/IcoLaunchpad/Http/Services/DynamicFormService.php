<?php

namespace Modules\IcoLaunchpad\Http\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\IcoLaunchpad\Entities\Form;
use Modules\IcoLaunchpad\Entities\SubmitFormDetails;
use Modules\IcoLaunchpad\Entities\SubmitFormLists;
use Modules\IcoLaunchpad\Http\Repositories\DynamicFormRepository;
use App\Jobs\MailSend;
use App\User;

class DynamicFormService
{
    private $dynamicFormRepository;

    public function __construct()
    {
        $this->dynamicFormRepository = new DynamicFormRepository();
    }

    public function saveDynamicForm($request)
    {
        $response = ['success' => false, 'message' => __('Something went wrong')];
        DB::beginTransaction();
        try {
            $user = auth()->user() ?? auth()->guard('api')->user();
            $user_submit = SubmitFormLists::where(['user_id' => $user->id, 'status' => STATUS_PENDING])->get()->count();
            if ($user_submit > 0) return ['success' => false, 'message' => __('You already have pending request')];

            $size = sizeof($request->ids);
            $formIds = $request->ids;
            $formValues = $request->values;
            $data = [];
            if ($size > 0) {
                $dynamicForm = Form::orderBy('id', 'asc')->get();
                if (isset($dynamicForm[0])) {
                    $checkId = $this->checkDynamicForm($dynamicForm, $formIds);
                    if ($checkId['success'] == false) {
                        return $checkId;
                    }

                    $unique_id = 10000 + SubmitFormDetails::groupBy('unique_id')->get()->count();
                    $submit_form = new SubmitFormLists;
                    $submit_form->user_id = $user->id;
                    $submit_form->status = STATUS_PENDING;
                    $submit_form->unique_id = $unique_id;
                    $submit_form->save();
                    $upload_Data = [];

                    for ($i = 0; $i < $size; $i++) {
                        $formVal = $this->checkDynamicFormValue($formValues, $formIds, $i);
                        if ($formVal['success'] == false) {
                            return $formVal;
                        } else {
                            $dynamicForm = Form::find($formIds[$i]);

                            $data['unique_id'] = $unique_id;
                            $data['question'] = $dynamicForm->title;

                            if ($dynamicForm->type == FORM_INPUT_TEXT || $dynamicForm->type == FORM_TEXT_AREA) {
                                $data['is_input'] = true;
                                $data['is_option'] = false;
                                $data['is_file'] = false;
                                $data['answer'] = $formValues[$i];
                            }
                            if ($dynamicForm->type == FORM_SELECT || $dynamicForm->type == FORM_RADIO || $dynamicForm->type == FORM_CHECKBOX) {
                                $data_srting = null;
                                if (str_contains($formValues[$i], ",")) {
                                    $data_srting = explode(',', $formValues[$i]);
                                    $data_srting = json_encode($data_srting);
                                } else {
                                    $data_srting = [$formValues[$i]];
                                    $data_srting = json_encode($data_srting);
                                }
                                $data['is_input'] = false;
                                $data['is_option'] = true;
                                $data['is_file'] = false;
                                $data['answer'] = $data_srting;
                            }
                            if ($dynamicForm->type == FORM_FILE) {

                                $is_file = file_exists($formValues[$i] ?? '');

                                if ($is_file) {
                                    $extension = $formValues[$i]->getClientOriginalExtension();

                                    if ($dynamicForm->file_type == 'jpg_png' && !($extension == 'png' || $extension == 'PNG' || $extension == 'jpg' || $extension == 'JPG')) {
                                        $response = ['success' => false, 'message' => $dynamicForm->title . ' ' . __('file must be jpg or png')];
                                        return $response;
                                    }
                                    if ($dynamicForm->file_type == 'pdf_word' && !($extension == 'pdf' || $extension == 'doc')) {
                                        $response = ['success' => false, 'message' => $dynamicForm->title . ' ' . __('file must be word or pdf')];
                                        return $response;
                                    }
                                    $fileName = uploadAnyFile($formValues[$i], FILE_ICO_STORAGE_PATH);

                                    $data['is_input'] = false;
                                    $data['is_option'] = false;
                                    $data['is_file'] = true;

                                    $data['answer'] = asset(FILE_ICO_VIEW_PATH . $fileName);
                                } else {
                                    $data['is_input'] = false;
                                    $data['is_option'] = false;
                                    $data['is_file'] = true;

                                    $data['answer'] = null;
                                }
                            }
                            $upload_Data[] = $data;
                        }
                    }
                }

                SubmitFormDetails::insert($upload_Data);

                $admin_user = User::where('role', USER_ROLE_ADMIN)->first();
                $data['mailTemplate'] = 'email.ico_form_accept_reject';
                $data['name'] = $admin_user->first_name . ' ' . $admin_user->last_name;
                $data['subject'] = 'New form submitted';
                $data['details'] = 'New Form submitted by user name:' . $user->first_name . ' ' . $user->last_name . 'user email:' . $user->email;
                $data['to'] = $admin_user->email;

                dispatch(new MailSend($data));

                $response = responseData(true, __("Form is submitted successfully"));
            } else {
                $response = responseData(false, __('Data is required'));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            storeException("DynamicFromSubmit: ", $e->getMessage());
            $response = responseData(false, __($e->getLine()));
        }
        DB::commit();
        return $response;
    }

    // check dynamic form id
    public function checkDynamicForm($dynamicForm, $formIds)
    {
        foreach ($dynamicForm as $item) {
            if (!in_array($item->id, $formIds) && ($item->required == STATUS_ACTIVE)) {
                $response = responseData(false, $item->title . __(' is required'));
                return $response;
            }
        }
        $response = responseData(true, __('success'));
        return $response;
    }

    // check dynamic form data value
    public function checkDynamicFormValue($formValues, $formIds, $i)
    {
        $formItem = Form::where(['id' => $formIds[$i]])->first();
        if (empty($formItem)) {
            $response = responseData(false, __('item not found ') . $formIds[$i]);
            return $response;
        }
        if (($formItem->required == STATUS_ACTIVE) && empty($formValues[$i])) {
            $response = responseData(false, $formItem->title . __(' value is required'), ['id' => $formItem->id, 'sl' => $i]);
            return $response;
        }

        $response = responseData(true, __('success'));
        return $response;
    }


    public function getSubmittedFromListForUser($per_page)
    {
        $response = $this->dynamicFormRepository->getSubmittedFromListForUser($per_page);
        return $response;
    }
}
