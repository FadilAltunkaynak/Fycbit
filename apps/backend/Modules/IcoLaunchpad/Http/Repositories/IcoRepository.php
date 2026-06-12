<?php

namespace Modules\IcoLaunchpad\Http\Repositories;

use App\Model\Coin;
use App\Model\Network;
use Modules\IcoLaunchpad\Entities\Temp;
use Modules\IcoLaunchpad\Entities\IcoToken;
use Modules\IcoLaunchpad\Entities\IcoPhaseInfo;
use Modules\IcoLaunchpad\Entities\TokenBuyHistory;
use Modules\IcoLaunchpad\Entities\ICOTokenTranslation;

class IcoRepository
{

    public function getListICOToken($per_page = null)
    {
        $limit = $per_page ?? null;
        return IcoToken::latest()->paginate($limit);
    }

    public function getActiveICOList($per_page = 200, $extra = null)
    {
        $lang_key = isset($extra['lang_key']) ? $extra['lang_key'] : 'en';
        $limit = $per_page ?? 200;
        $ico_phase_infos = IcoToken::with([
            'translationICO' => function ($query) use ($lang_key) {
                $query->where('lang_key', $lang_key);
            }
        ])->where('status', STATUS_ACTIVE)->latest()->paginate($limit);

        $ico_phase_infos->map(function ($query) use ($extra) {
            if (isset($query->image)) {
                $query->image = asset(path_image() . $query->image);
            }
            if (isset($extra['api']) && $extra['api'] == true) {
                if ($query->translationICO->count() > 0) {
                    $query->details_rule = $query->translationICO[0]->details_rule;
                }
            }
        });
        return $ico_phase_infos;
    }

    public function getUserICOList($per_page = null, $search = null)
    {
        $user = auth()->user() ?? auth()->guard('api')->user();
        $limit = $per_page ?? null;
        $ico_token_list = IcoToken::where('user_id', $user->id)
            ->when(isset($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->orWhere('token_name', 'LIKE', '%' . $search . '%')
                        ->orWhere('base_coin', 'LIKE', '%' . $search . '%')
                        ->orWhere('wallet_address', 'LIKE', '%' . $search . '%');
                });
            })
            ->paginate($limit);

        return $ico_token_list;
    }

    public function storeUpdateICO($id, $data)
    {
        try {
            if (isset($id)) {
                $user = auth()->user() ?? auth()->guard('api')->user();
                $ico_token = IcoToken::where('id', $id)->first();

                if ($user->id != $ico_token->user_id) {
                    $response = ['success' => true, 'message' => __('You have no access to update this token')];
                    return $response;
                }

                $data = IcoToken::where('id', $id)->update($data);
                $response = ['success' => true, 'message' => __('ICO Token is updated successfully!'), 'data' => $data];
            } else {
                $data = IcoToken::create($data);
                $response = ['success' => true, 'message' => __('ICO Token is stored successfully!'), 'data' => $data];
            }
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('New ICO Token is not store!')];
            storeException("storeNewICO", $e->getMessage());
        }
        return $response;
    }

    public function storeUpdateICORequest($id, $data)
    {
        try {
            $ico_token_details = IcoToken::find($id);

            if (isset($ico_token_details)) {
                if ($ico_token_details->approved_status == STATUS_PENDING) {
                    $response = ['success' => true, 'message' => __('You can update ICO Token after it is accepted!')];
                    return $response;
                }
                $except_array = ['status', 'approved_id', 'approved_status'];

                foreach ($data as $key => $value) {
                    if (!in_array($key, $except_array) && $ico_token_details->$key != $value) {
                        $check_temp = Temp::where('update_table_type', ICO_TOKEN_TABLE)
                            ->where('update_table_type_id', $ico_token_details->id)
                            ->where('column_name', $key)
                            ->where('status', STATUS_PENDING)->first();

                        if (isset($check_temp)) {
                            $check_temp->requested_value = $value;
                            $check_temp->status = STATUS_PENDING;
                            $check_temp->save();
                        } else {
                            $temp_value = new Temp;
                            $temp_value->update_table_type = ICO_TOKEN_TABLE;
                            $temp_value->update_table_type_id = $ico_token_details->id;
                            $temp_value->column_name = $key;
                            $temp_value->previous_value = $ico_token_details->$key;
                            $temp_value->requested_value = $value;
                            $temp_value->save();
                        }

                        $ico_token_details->is_updated = STATUS_ACTIVE;
                        $ico_token_details->save();
                    }
                }
                $ico_token_details->is_updated = STATUS_ACTIVE;
                $ico_token_details->save();

                $response = ['success' => true, 'message' => __('Your ICO Token updated request is sent to admin successfully!'), 'data' => $ico_token_details];
            } else {
                $response = ['success' => false, 'message' => __('Your ICO Token is not found!')];
            }
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("storeUpdateICORequest", $e->getMessage());
        }
        return $response;
    }

    public function icoFeaturedStatusChange($id)
    {
        try {
            $ico_phase_info = IcoPhaseInfo::find($id);
            if ($ico_phase_info->is_featured == STATUS_ACTIVE) {
                $ico_phase_info->update(['is_featured' => STATUS_INACTIVE]);
            } else {
                $ico_phase_info->update(['is_featured' => STATUS_ACTIVE]);
            }
            $response = ['success' => true, 'message' => __('Featured status Changed Successfully!')];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Featured status is not Changed!')];
            storeException("icoFeaturedStatusChange", $e->getMessage());
        }

        return $response;
    }

    public function icoTokenStatusChange($id)
    {
        try {
            $ico_token = IcoToken::find($id);
            if ($ico_token->status == STATUS_ACTIVE) {
                $ico_token->update(['status' => STATUS_INACTIVE]);
            } else {
                $ico_token->update(['status' => STATUS_ACTIVE]);
            }
            $response = ['success' => true, 'message' => __('Status Changed Successfully!')];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Status is not Changed!')];
            storeException("icoStatusChange", $e->getMessage());
        }

        return $response;
    }

    public function findICOTokenByID($id, $extra = null)
    {
        try {
            $lang_key = isset($extra['lang_key']) ? $extra['lang_key'] : 'en';

            $ico_phase_token = IcoToken::with([
                'user',
                'translationICO' => function ($query) use ($lang_key) {
                    $query->where('lang_key', $lang_key);
                }
            ])->find($id);

            if (isset($extra['api']) && $extra['api'] == true) {
                if (isset($ico_phase_token->translationICO[0]) && $ico_phase_token->translationICO->count() > 0) {
                    $ico_phase_token->details_rule = $ico_phase_token?->translationICO[0]->details_rule;
                }
            }
            $data['ico_phase_token'] = $ico_phase_token;
            $data['networks'] = Network::whereStatus(STATUS_ACTIVE)->get();
            $response = ['success' => true, 'message' => __('ICO Token details'), 'data' => $data];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("findICOByID", $e->getMessage());
        }
        return $response;
    }

    public function deleteICOTokenByID($id)
    {
        try {
            $ico_phase_info = IcoToken::find($id);
            $ico_phase_info->delete();
            $response = ['success' => true, 'message' => __('ICO Token is deleted Successfully!')];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('ICO is not deleted!')];
            storeException("deleteICOByID", $e->getMessage());
        }
        return $response;
    }

    public function buyICOToken($data)
    {
        try {
            IcoPhaseInfo::where('id', $data['token_id'])
                ->increment('total_sell_token_supply', $data['amount']);

            TokenBuyHistory::create($data);
            $response = ['success' => true, 'message' => __('ICO buy successfully!')];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('ICO is not buy!')];
            storeException("buyICOToken", $e->getMessage());
        }
        return $response;
    }


    public function saveCoinByICORequest($ico_id, $data)
    {
        $coin_details = Coin::where('ico_id', $ico_id)->first();
        if (isset($coin_details)) {
            $accept_array = ['name', 'coin_type', 'network', 'coin_price'];
            foreach ($data as $key => $value) {
                if (in_array($key, $accept_array) && $coin_details->$key != $value) {
                    $check_temp = Temp::where('update_table_type', COIN_TABLE)
                        ->where('update_table_type_id', $ico_id)
                        ->where('column_name', $key)
                        ->where('status', STATUS_PENDING)->first();

                    if (isset($check_temp)) {
                        $check_temp->requested_value = $value;
                        $check_temp->status = STATUS_PENDING;
                        $check_temp->save();
                    } else {
                        $temp_value = new Temp;
                        $temp_value->update_table_type = COIN_TABLE;
                        $temp_value->update_table_type_id = $ico_id;
                        $temp_value->column_name = $key;
                        $temp_value->previous_value = $coin_details->$key;
                        $temp_value->requested_value = $value;
                        $temp_value->save();
                    }
                }
            }
        } else
            return responseData(false, __("Failed"));
        return responseData(true, __("Success"), $coin_details ?? []);
    }

    public function getTokenDetailsTranslationByLangKey($token_id, $lang_key)
    {
        $ico_token_translation = ICOTokenTranslation::where('ico_token_id', $token_id)
            ->where('lang_key', $lang_key)->first();
        $response = ['success' => true, 'message' => __('Token Translation details'), 'data' => $ico_token_translation];
        return $response;
    }
    public function updateICOTokenLanguage($request)
    {
        try {
            $ico_token_translation = ICOTokenTranslation::where('ico_token_id', $request->ico_token_id)
                ->where('lang_key', $request->lang_key)->first();

            if (!isset($ico_token_translation)) {
                $ico_token_translation = new ICOTokenTranslation;
                $ico_token_translation->ico_token_id = $request->ico_token_id;
                $ico_token_translation->lang_key = $request->lang_key;
            }

            $ico_token_translation->details_rule = $request->details_rule;
            $ico_token_translation->save();
            return responseData(true, __('ICO token Language Text is Updated'));
        } catch (\Exception $e) {
            storeException('updateICOTokenLanguage', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }


    }
}
