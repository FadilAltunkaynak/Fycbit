<?php

namespace App\Http\Services\DataTable;

use App\Enums\DepositCollectionStatus;
use App\Enums\DepositeStatus;
use App\Enums\NetworkBase;
use App\Model\AdminReceiveTokenTransactionHistory;
use App\Model\DepositeTransaction;
use App\Model\EstimateGasFeesTransactionHistory;
use App\Traits\DateFormatTrait;
use App\Traits\NumberFormatTrait;

class DepositHistoryDataTableService
{
    use NumberFormatTrait, DateFormatTrait;

    public function getData($status = null)
    {
        $deposit = DepositeTransaction::orderBy('id', 'desc')
            ->when($status != null, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->select('deposite_transactions.*');

        return datatables()->of($deposit)
            ->filterColumn('id', function ($query, $keyword) {
                if(!str_contains($keyword, '@')) return;
                $query->with(['receiverWallet','receiverWallet.user'])->whereHas('receiverWallet.user', function($q) use ($keyword){
                    return $q->where('email', "LIKE", "%$keyword%");
                });
            })
            ->filterColumn('status', function ($query, $keyword) {
                $keyword = strtolower($keyword);
                $matchingCodes = array_keys(array_filter(DepositeStatus::getList(), function ($label) use ($keyword) {
                    return strpos(strtolower($label), $keyword) != false;
                }));
                if (!empty($matchingCodes)) {
                    $query->whereIn('status', $matchingCodes);
                }
            })
            ->filterColumn('address_type', function ($query, $keyword) {
                $keyword = strtolower($keyword);
                $matchingCodes = array_keys(array_filter(addressType(), function ($label) use ($keyword) {
                    return strpos(strtolower($label), $keyword) != false;
                }));
                if (!empty($matchingCodes)) {
                    $query->whereIn('address_type', $matchingCodes);
                }
            })
            ->editColumn('amount', function ($item) {
                return $this->truncateNum($item->amount);
            })
            ->editColumn('status', function ($item) {
                return DepositeStatus::tryFrom($item->status)->statusHtml();
            })
            ->editColumn('address_type', function ($item) {
                return addressType($item->address_type);
            })
            ->editColumn('updated_at', function ($item) {
                return $this->dateFormat($item->updated_at);
            })
            ->addColumn('actions', function ($item) {
                return [
                    'id' => encrypt($item->id),
                ];
            })
            ->rawColumns(['status', 'address_type', 'sender_user_email', 'receiver_wallet_id', 'actions'])
            ->make(true);
    }

    public function adminPendingDepositHistory()
    {
        $items = DepositeTransaction::with('networkInfo')
            ->whereHas('networkInfo', function ($query) {
                $query->whereIn('base_type', NetworkBase::customNetworkGroup());
            })
            ->where(['address_type' => ADDRESS_TYPE_EXTERNAL])
            ->whereIn('is_admin_receive', [DepositCollectionStatus::PENDING->value, DepositCollectionStatus::PROCESSING->value])
            ->orderBy('id', 'desc');

        return datatables()->of($items)
            ->editColumn('created_at', function ($item) {
                return $this->dateFormat($item->created_at);
            })
            ->editColumn('status', function ($item) {
                return DepositCollectionStatus::tryFrom($item->is_admin_receive)->statusHtml();
            })
            ->editColumn('amount', function ($item) {
                return $this->truncateNum($item->amount);
            })
            ->addColumn('actions', function ($item) {
                $data = [
                    'acceptRoute' => route('adminPendingDepositAccept', encrypt($item->id)),
                    'rejectNote' => $item->reject_note ?? null,
                    'status' => $item->is_admin_receive,
                ];
                return view('renderables.deposit_collection_action', compact('data'))->render();
            })
            ->rawColumns(['actions', 'status'])
            ->make(true);
    }

    public function adminTokenReceiveHistory()
    {
        $items = AdminReceiveTokenTransactionHistory::with('deposit')->latest();

        return datatables()->of($items)
            ->editColumn('amount', function ($item) {
                return $this->truncateNum($item->amount);
            })
            ->editColumn('created_at', function ($item) {
                return $this->dateFormat($item->created_at);
            })
            ->editColumn('status', function ($item) {
                return DepositeStatus::tryFrom($item->status)->statusHtml();
            })
            ->rawColumns(['status'])
            ->make(true);
    }

    public function adminGasSendHistory()
    {
        $items = EstimateGasFeesTransactionHistory::with('deposit')->latest();

        return datatables()->of($items)
            ->editColumn('created_at', function ($item) {
                return $this->dateFormat($item->created_at);
            })
            ->addColumn('status', function ($item) {
                return DepositeStatus::tryFrom($item->status)->statusHtml();
            })
            ->rawColumns(['status'])
            ->make(true);
    }
}
