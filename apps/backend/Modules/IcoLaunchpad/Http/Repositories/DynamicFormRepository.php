<?php

namespace Modules\IcoLaunchpad\Http\Repositories;

use Modules\IcoLaunchpad\Entities\SubmitFormLists;
use Modules\IcoLaunchpad\Entities\SubmitFormDetails;
use Modules\IcoLaunchpad\Entities\IcoToken;

class DynamicFormRepository
{

    public function getSubmittedFromListForUser($per_page = null)
    {
        try {
            $limit = $per_page ?? null;
            $user = auth()->user() ?? auth()->guard('api')->user();
            $form_list = SubmitFormLists::with(['formDetails'])->where('user_id', $user->id)->latest()->paginate($limit);
            $form_ids = SubmitFormLists::where('user_id', $user->id)->pluck('id')->toArray();
            $check_token_form_ids = IcoToken::whereIn('form_id', $form_ids)
                ->whereIn('approved_status', [STATUS_PENDING, STATUS_ACCEPTED, STATUS_MODIFICATION])
                ->pluck('form_id')->toArray();

            $form_list->map(function ($query) use ($check_token_form_ids) {
                if (in_array($query->id, $check_token_form_ids)) {
                    $query->token_create_status = 0;
                } else {
                    $query->token_create_status = 1;
                }
            });

            $response = ['success' => true, 'message' => __('Submitted form list by user'), 'data' => $form_list];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("getSubmittedFromListForUser", $e->getMessage());
        }
        return $response;
    }
}
