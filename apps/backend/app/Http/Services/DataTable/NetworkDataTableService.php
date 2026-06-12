<?php

namespace App\Http\Services\DataTable;

use App\Model\Network;

class NetworkDataTableService
{
    public function getData()
    {
        $networks = Network::with('block')->orderBy('name');
        return datatables()->of($networks)
            ->editColumn('logo', function ($item) {
                $logo = !empty($item->logo) ? asset(IMG_NETWORK_LOGO_PATH . $item->logo) : asset("assets/img/dlr.png");
                return '<img src="' . $logo . '" height="40px">';
            })
            ->editColumn('base_type', function ($item) {
                return getBaseNetworkType($item->base_type);
            })
            ->editColumn('status', function ($item) {
                return '<label class="switch">
                            <input onchange="changeNetworkStatus(' . $item->id . ')" type="checkbox" name="status" ' . (($item->status == STATUS_ACTIVE) ? "checked" : "") . ' />
                            <span class="slider"></span>
                        </label>';
            })
            ->addColumn('actions', function ($item) {
                return '<div class="activity-icon"><ul>'
                    . edit_html("createNetwork", $item->id)
                    . (empty(@$item->block->error) ? '' : '<li class="viewuser"><a title="' . __('Block error') . '" href="' . route('createNetwork', encrypt($item->id)) . '" class="text-danger"><i class="fa fa-exclamation-circle fa-2x"></i></a></li>')
                    . '</ul></div>';
            })
            ->rawColumns(['logo', 'status', 'actions'])
            ->make(true);
    }
}
