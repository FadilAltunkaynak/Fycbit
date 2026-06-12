<?php

namespace Modules\IcoLaunchpad\Http\Repositories;

use Modules\IcoLaunchpad\Entities\Temp;
use Modules\IcoLaunchpad\Entities\IcoToken;
use Modules\IcoLaunchpad\Entities\IcoPhaseInfo;
use App\Model\Coin;

class UpdateRequestRepository
{

    public function getUpdateRequestDetails($type, $id)
    {
        if ($type == ICO_TOKEN_TABLE) {
            $data1 = Temp::where('update_table_type', $type)->where('update_table_type_id', $id)->where('status', STATUS_PENDING)->get();
            $data2 = Temp::where('update_table_type', COIN_TABLE)->where('update_table_type_id', $id)->where('status', STATUS_PENDING)->get();
            $data = $data1->merge($data2);
        } else {
            $data = Temp::where('update_table_type', $type)->where('update_table_type_id', $id)->where('status', STATUS_PENDING)->get();
        }

        $response = ['success' => true, 'message' => __('updated request info list'), 'data' => $data];
        return $response;
    }

    public function updatedRequestedAcceptedByID($id)
    {
        $updated_request_info = Temp::find($id);
        if (isset($updated_request_info)) {
            if ($updated_request_info->update_table_type == ICO_TOKEN_TABLE) {
                $details = IcoToken::find($updated_request_info->update_table_type_id);
            } else if ($updated_request_info->update_table_type == ICO_TOKEN_PHASE_TABLE) {
                $details = IcoPhaseInfo::find($updated_request_info->update_table_type_id);
            } else if ($updated_request_info->update_table_type == COIN_TABLE) {
                $details = Coin::where('ico_id', $updated_request_info->update_table_type_id)->first();
            }

            $updated_request_info->status = STATUS_ACCEPTED;
            $updated_request_info->save();

            $details[$updated_request_info->column_name] = $updated_request_info->requested_value;

            $check_temp = Temp::where('update_table_type', $updated_request_info->update_table_type)
                ->where('update_table_type_id', $updated_request_info->update_table_type_id)
                ->where('status', STATUS_PENDING)->get();

            if ($updated_request_info->update_table_type != COIN_TABLE && $check_temp->count() == 0) {
                $details->is_updated = STATUS_INACTIVE;
            }
            $details->save();
            $response = ['success' => true, 'message' => __('Request accepted successfully!')];
        } else {
            $response = ['success' => false, 'message' => __('Access Denied!')];
        }
        return $response;
    }

    public function updatedRequestedRejectedByID($id)
    {
        $updated_request_info = Temp::find($id);
        if (isset($updated_request_info)) {

            $updated_request_info->status = STATUS_REJECTED;
            $updated_request_info->save();

            $check_temp = Temp::where('update_table_type', ICO_TOKEN_TABLE)
                ->where('update_table_type_id', $updated_request_info->update_table_type_id)
                ->where('status', STATUS_PENDING)->get();

            if ($check_temp->count() == 0) {
                if ($updated_request_info->update_table_type == ICO_TOKEN_TABLE) {
                    $details = IcoToken::find($updated_request_info->update_table_type_id);
                } elseif ($updated_request_info->update_table_type == ICO_TOKEN_PHASE_TABLE) {
                    $details = IcoPhaseInfo::find($updated_request_info->update_table_type_id);
                }

                $details->is_updated = STATUS_INACTIVE;
                $details->save();
            }

            $response = ['success' => true, 'message' => __('Request rejected successfully!')];
        } else {
            $response = ['success' => false, 'message' => __('Access Denied!')];
        }
        return $response;
    }
}
