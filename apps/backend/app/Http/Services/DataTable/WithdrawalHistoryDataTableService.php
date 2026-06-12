<?php

namespace App\Http\Services\DataTable;

use App\Enums\WithdrawStatus;
use App\Model\WithdrawHistory;
use App\Traits\DateFormatTrait;
use App\Traits\NumberFormatTrait;

class WithdrawalHistoryDataTableService
{
    use NumberFormatTrait, DateFormatTrait;

    public function getData($status = null)
    {
        $withdrawal = WithdrawHistory::orderBy('id', 'desc')
            ->when(isset($status), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->select('withdraw_histories.*');

        return datatables()->of($withdrawal)
            ->filterColumn('id', function ($query, $keyword) {
                if(!str_contains($keyword, '@')) return;
                $query->with('user')->whereHas('user', function($q) use ($keyword){
                    return $q->where('email', $keyword);
                });
            })
            ->filterColumn('status', function ($query, $keyword) {
                $keyword = strtolower($keyword);
                $matchingCodes = array_keys(array_filter(WithdrawHistory::STATUS_TEXT, function ($label) use ($keyword) {
                    return strpos(strtolower($label), $keyword) !== false;
                }));
                if (!empty($matchingCodes)) {
                    $query->whereIn('status', $matchingCodes);
                }
            })
            ->filterColumn('address_type', function ($query, $keyword) {
                $keyword = strtolower($keyword);
                $matchingCodes = array_keys(array_filter(addressType(), function ($label) use ($keyword) {
                    return strpos(strtolower($label), $keyword) !== false;
                }));
                if (!empty($matchingCodes)) {
                    $query->whereIn('address_type', $matchingCodes);
                }
            })
            ->editColumn('status', function ($item) {
                return WithdrawStatus::tryFrom($item->status)->statusHtml();
            })
            ->editColumn('address_type', function ($item) {
                return addressType($item->address_type);
            })
            ->editColumn('amount', function ($item) {
                return $this->truncateNum($item->amount);
            })
            ->editColumn('coin_type', function ($item) {
                return find_coin_type($item->coin_type);
            })
            ->editColumn('updated_at', function ($item) {
                return $this->dateFormat($item->updated_at);
            })
            ->addColumn('actions', function ($item) {
                $data = [
                    'id' => encrypt($item->id),
                    'rejectRoute' => route('adminRejectPendingWithdrawal'),
                    'status' => $item->status,
                ];
                switch ($item->status) {
                    case WithdrawStatus::PENDING->value:
                        $data['acceptRoute'] = route('adminAcceptPendingWithdrawal', encrypt($item->id));
                        break;
                    case WithdrawStatus::FAILED->value:
                        $data['acceptRoute']   = route('adminAcceptPendingWithdrawal', encrypt($item->id));
                        $data['asAcceptRoute'] = route('adminMakeAsWithdrawalSuccess');
                        $data['status']        = $item->status;
                        break;
                }
                return view('renderables.withdrawal_list_action', compact('data'))->render();
            })
            ->rawColumns(['status', 'address_type', 'coin_type', 'updated_at', 'actions'])
            ->make(true);
    }
}
