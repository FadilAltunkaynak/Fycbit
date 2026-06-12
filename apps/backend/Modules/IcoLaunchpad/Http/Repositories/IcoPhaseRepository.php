<?php

namespace Modules\IcoLaunchpad\Http\Repositories;

use Modules\IcoLaunchpad\Entities\IcoPhaseInfo;
use Modules\IcoLaunchpad\Entities\Temp;
use Carbon\Carbon;
use App\Jobs\MailSend;
use App\Model\Coin;
use App\User;
use Modules\IcoLaunchpad\Entities\ICOTokenTranslation;
use Modules\IcoLaunchpad\Entities\TokenBuyHistory;
use App\Model\Network;

class IcoPhaseRepository
{

    public function getICOPhaseActiveList($request)
    {
        try {
            $today = Carbon::today()->toDateString();
            $limit = $request->per_page ?? null;
            $type = $request->type ?? 1;

            $data = IcoPhaseInfo::join('ico_tokens', 'ico_tokens.id', '=', 'ico_phase_infos.ico_token_id')
                ->where('ico_phase_infos.status', STATUS_ACTIVE)
                ->where('ico_phase_infos.end_date', '>', $today)
                ->when($type != PHASE_SORT_BY_FUTURE, function ($query) use ($today) {
                    $query->where('ico_phase_infos.end_date', '>=', $today);
                })
                ->when($type == PHASE_SORT_BY_EXPIRED, function ($query) use ($today) {
                    $query->where('ico_phase_infos.start_date', '<', $today)
                        ->orderBy('ico_phase_infos.end_date');
                })
                ->when($type == PHASE_SORT_BY_FEATURED, function ($query) use ($today) {
                    $query->where('ico_phase_infos.is_featured', STATUS_ACTIVE)
                        ->where('ico_phase_infos.start_date', '<=', Carbon::now())
                        ->where('ico_phase_infos.end_date', '>=', Carbon::now())
                        ->orderBy('ico_phase_infos.end_date');
                })
                ->when($type == PHASE_SORT_BY_RECENT, function ($query) use ($today) {
                    $query->where('ico_phase_infos.start_date', '<=', Carbon::now())
                        ->where('ico_phase_infos.end_date', '>=', Carbon::now())
                        ->orderBy('ico_phase_infos.end_date', 'asc');
                })
                ->when($type == PHASE_SORT_BY_FUTURE, function ($query) use ($today) {
                    $query->where('ico_phase_infos.start_date', '>', $today);
                })
                ->select(
                    'ico_phase_infos.*',
                    'ico_tokens.token_name',
                    'ico_tokens.coin_type',
                    'ico_tokens.base_coin',
                    'ico_tokens.network',
                    'ico_tokens.contract_address',
                    'ico_tokens.id as token_id'
                )
                ->get();

            $data->map(function ($query) {
                if (isset($query->image)) {
                    $query->image = asset(FILE_ICO_VIEW_PATH . $query->image);
                }
                if (isset($query->network)) {
                    $query->network = api_settings($query->network);
                }
                $query->total_participated = phaseTotalParticipated($query->id);
                $query->sold_phase_tokens = checkSoldIcoToken($query->id);
            });

            $response = ['success' => true, 'message' => __('ICO phases list.'), 'data' => $data];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("getICOPhaseActiveList", $e->getTraceAsString());
        }
        return $response;
    }

    public function getICOPhasesList($icoTokenId, $per_page = null)
    {
        try {
            $limit = $per_page ?? null;
            $data = IcoPhaseInfo::join('ico_tokens', 'ico_tokens.id', '=', 'ico_phase_infos.ico_token_id')
                ->where('ico_phase_infos.ico_token_id', $icoTokenId)
                ->select(
                    'ico_phase_infos.*',
                    'ico_tokens.token_name',
                    'ico_tokens.coin_type',
                    'ico_tokens.base_coin',
                    'ico_tokens.network',
                    'ico_tokens.contract_address',
                    'ico_tokens.id as token_id'
                )
                ->paginate($limit);
            $data->map(function ($query) {
                if (isset($query->image)) {
                    $query->image = asset(FILE_ICO_VIEW_PATH . $query->image);
                }
                if (isset($query->network)) {
                    $query->network = api_settings($query->network);
                }
                $query->total_participated = phaseTotalParticipated($query->id);
                $query->sold_phase_tokens = checkSoldIcoToken($query->id);
            });
            $response = ['success' => true, 'message' => __('ICO Token phases list.'), 'data' => $data];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("storeNewICO", $e->getMessage());
        }
        return $response;
    }

    public function getActiveICOPhaseDetails($request, $extra = null)
    {
        try {
            $lang_key = isset($extra['lang_key']) ? $extra['lang_key'] : 'en';

            $today = Carbon::today()->toDateString();
            $id = $request->id ?? null;
            $data = IcoPhaseInfo::with(['icoPhaseAdditionalDetails'])
                ->join('ico_tokens', 'ico_tokens.id', '=', 'ico_phase_infos.ico_token_id')
                ->select(
                    'ico_phase_infos.*',
                    'ico_tokens.token_name',
                    'ico_tokens.coin_type',
                    'ico_tokens.base_coin',
                    'ico_tokens.network',
                    'ico_tokens.contract_address',
                    'ico_tokens.website_link',
                    'ico_tokens.details_rule',
                    'ico_tokens.id as token_id'
                )
                ->find($id);


            if (isset($data)) {
                $data->image = isset($data->image) ? asset(FILE_ICO_VIEW_PATH . $data->image) : null;
                // $data->network = api_settings($data->network);

                $network = Network::find($data->network);
                $data->network = $network->name;

                $data->sold_phase_tokens = checkSoldIcoToken($data->id);

                if ($data->start_date < $today) {
                    $data->available_status = PHASE_AVAILABLE_STATUS_EXPIRED;
                } elseif ($data->start_date > $today && $data->end_date < $today) {
                    $data->available_status = PHASE_AVAILABLE_STATUS_ACTIVE;
                } elseif ($data->end_date > $today) {
                    $data->available_status = PHASE_AVAILABLE_STATUS_FUTURE;
                }
                $data->total_participated = phaseTotalParticipated($data->id);

                if (isset($extra['api']) && $extra['api'] == true && $lang_key != 'en') {
                    $translation = ICOTokenTranslation::where('ico_token_id', $data->token_id)
                        ->where('lang_key', $lang_key)->first();
                    if (isset($translation)) {
                        $data->details_rule = $translation->details_rule;
                    }

                }
            }
            $response = ['success' => true, 'message' => __('ICO phases details.'), 'data' => $data];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("storeNewICO", $e->getMessage());
        }
        return $response;
    }

    public function getICOPhaseDetailsById($icoPhaseId)
    {
        try {
            $data = IcoPhaseInfo::with(['icoPhaseAdditionalDetails'])->find($icoPhaseId);
            $response = ['success' => true, 'message' => __('ICO Token phases details.'), 'data' => $data];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("storeNewICO", $e->getMessage());
        }
        return $response;
    }

    public function storeUpdateICOPhase($id, $data)
    {
        try {
            if (isset($id) && $id != "null") {
                $data = IcoPhaseInfo::where('id', $id)->update($data);
                $response = ['success' => true, 'message' => __('Updated ICO Phase info successfully!'), 'data' => $data];
            } else {
                $data = IcoPhaseInfo::create($data);
                $response = ['success' => true, 'message' => __('Store new ICO Phase info successfully!'), 'data' => $data];
            }
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('ICO Phase info is not store!')];
            storeException("storeNewICO", $e->getMessage());
        }
        return $response;
    }

    public function storeUpdateRequestICOPhase($id, $data)
    {
        try {
            $ico_phase_details = IcoPhaseInfo::find($id);

            if (isset($ico_phase_details)) {
                $flag = 0;
                foreach ($data as $key => $value) {
                    if ($ico_phase_details->$key != $value) {
                        $flag = 1;
                        $check_temp = Temp::where('update_table_type', ICO_TOKEN_PHASE_TABLE)
                            ->where('update_table_type_id', $ico_phase_details->id)
                            ->where('column_name', $key)
                            ->where('status', STATUS_PENDING)->first();

                        if (isset($check_temp)) {
                            $check_temp->requested_value = $value;
                            $check_temp->status = STATUS_PENDING;
                            $check_temp->save();
                        } else {
                            $temp_value = new Temp;
                            $temp_value->update_table_type = ICO_TOKEN_PHASE_TABLE;
                            $temp_value->update_table_type_id = $ico_phase_details->id;
                            $temp_value->column_name = $key;
                            $temp_value->previous_value = $ico_phase_details->$key;
                            $temp_value->requested_value = $value;
                            $temp_value->save();
                        }
                        $ico_phase_details->is_updated = STATUS_ACTIVE;
                        $ico_phase_details->save();
                    }
                }

                if ($flag == 1) {
                    $user = User::where('id', $ico_phase_details->user_id)->first();
                    $admin_user = User::where('role', USER_ROLE_ADMIN)->first();
                    $data['mailTemplate'] = 'email.ico_form_accept_reject';
                    $data['name'] = $admin_user->first_name . ' ' . $admin_user->last_name;
                    $data['subject'] = 'ICO Token Phase updated request';
                    $data['details'] = 'ICO Token Phase updated request send by User Name:' . $user->first_name . ' ' . $user->last_name . ', User Email:' . $user->email . ',
                                       Token ID:' . $ico_phase_details->ico_token_id;
                    $data['to'] = $admin_user->email;

                    dispatch(new MailSend($data));
                }


                $response = ['success' => true, 'message' => __('Your ICO Token Phase updated request is sent to admin successfully!')];
            } else {
                $response = ['success' => false, 'message' => __('Your ICO Token Phase is not found!')];
            }
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("storeUpdateRequestICOPhase", $e->getMessage());
        }
        return $response;
    }

    public function deleteICOPhaseById($icoPhaseId)
    {
        try {
            $data = IcoPhaseInfo::find($icoPhaseId);
            $data->delete();
            $response = ['success' => true, 'message' => __('ICO Token phases delete.')];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("storeNewICO", $e->getMessage());
        }
        return $response;
    }

    public function checkDateOfPhase($request)
    {
        try {
            $checkSelfDate = IcoPhaseInfo::where('ico_token_id', $request->ico_token_id)->where('id', $request->id)->first();
            if (isset($checkSelfDate) && $checkSelfDate->start_date == $request->start_date && $checkSelfDate->end_date == $request->end_date) {
                $response = ['success' => true, 'message' => __('You have no phase in between this date!')];
                return $response;
            }
            $today = Carbon::today()->toDateString();
            if (empty($request->id) && $request->start_date < $today) {
                $response = ['success' => false, 'message' => __('Start date can not be past from today!')];
                return $response;
            }
            if (isset($request->id)) {

                $checkDate = IcoPhaseInfo::where('ico_token_id', $request->ico_token_id)->where('id', '<>', $request->id)->get();
            } else {
                $checkDate = IcoPhaseInfo::where('ico_token_id', $request->ico_token_id)->get();
            }

            $flag = false;
            foreach ($checkDate as $date) {
                if (
                    $request->start_date == $date->start_date ||
                    $request->start_date == $date->end_date ||
                    $request->end_date == $date->start_date ||
                    $request->end_date == $date->end_date
                ) {
                    $flag = true;
                } else if ($request->end_date < $date->start_date) {
                    $flag = true;
                } else if ($request->start_date > $date->start_date && $request->end_date < $date->start_date) {
                    $flag = true;
                } else if ($request->start_date < $date->end_date) {
                    $flag = true;
                }
            }
            if ($flag == true) {
                $response = ['success' => false, 'message' => __('You have already a phase in between this date!')];
            } else {
                $response = ['success' => true, 'message' => __('You have no phase in between this date!')];
            }
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("checkDateOfPhase", $e->getMessage());
        }
        return $response;
    }

    public function changeICOPhaseFeatured($id)
    {
        try {
            $ico_token_phase = IcoPhaseInfo::find($id);
            if ($ico_token_phase->is_featured == STATUS_ACTIVE) {
                $ico_token_phase->update(['is_featured' => STATUS_INACTIVE]);
            } else {
                $ico_token_phase->update(['is_featured' => STATUS_ACTIVE]);
            }
            $response = ['success' => true, 'message' => __('Featured status Changed Successfully!')];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Status is not Changed!')];
            storeException("icoStatusChange", $e->getMessage());
        }

        return $response;
    }

    public function changeICOPhaseStatus($id)
    {
        try {
            $ico_token_phase = IcoPhaseInfo::find($id);
            if (isset($ico_token_phase)) {
                $check_listed_coin = Coin::where('ico_id', $ico_token_phase->ico_token_id)->where('is_listed', 1)->first();

                if (isset($check_listed_coin)) {
                    return responseData(false, __('You can not created new phase for this token as this token is listed!'));
                }

                if ($ico_token_phase->status == STATUS_ACTIVE) {
                    $ico_token_phase->update(['status' => STATUS_INACTIVE]);
                } else {
                    $ico_token_phase->update(['status' => STATUS_ACTIVE]);
                }
                $response = ['success' => true, 'message' => __('Status Changed Successfully!')];
            } else {
                $response = ['success' => false, 'message' => __('Invalid Request!')];
            }

        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Status is not Changed!')];
            storeException("icoStatusChange", $e->getMessage());
        }

        return $response;
    }
}
