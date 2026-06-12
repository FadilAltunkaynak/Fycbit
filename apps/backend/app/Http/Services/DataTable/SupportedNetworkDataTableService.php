<?php

namespace App\Http\Services\DataTable;

use App\Model\SupportedNetwork;

class SupportedNetworkDataTableService
{
    public function getData()
    {
        $networks = SupportedNetwork::query();
        return datatables()->of($networks)
            ->editColumn('status', function ($item) {
                return '<label class="switch">
                            <input onchange="changeNetworkStatus(' . $item->id . ')" type="checkbox" name="status" ' . (($item->status == STATUS_ACTIVE) ? "checked" : "") . ' />
                            <span class="slider"></span>
                        </label>';
            })
            ->editColumn("gas_limit", function ($item) {
                return @$item->gas_limit ?: 0;
            })
            ->addColumn('actions', function ($item) {
                return '<ul>' . editHtmlByRoute(
                    ($item->is_manually ?? false)
                        ? route("creteSupportedNetworkPage") . "?slug=$item->slug"
                        : route("supportedNetworkEdit", encrypt($item->id))
                ) . '</ul>';
            })
            ->rawColumns(['status', 'actions'])
            ->make(true);
    }
}
