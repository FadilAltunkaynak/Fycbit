<?php

namespace App\Http\Services\DataTable;

use App\Enums\CoinType;
use App\Model\CoinNetwork;
use App\Services\HtmlRenders\CoinNetworkActionRenderer;
use App\Traits\DateFormatTrait;
use App\Traits\NumberFormatTrait;

class CoinNetworkDataTableService
{
    use NumberFormatTrait, DateFormatTrait;

    public function getData()
    {
        $networks = CoinNetwork::with(["coin", "network"])->orderBy('id', 'desc');
        return datatables()->of($networks)
            ->addColumn('logo', function ($item) {
                $img = $item?->coin?->coin_icon;
                $logo = !empty($img) ? asset(path_image() . 'coin/' . $img) : asset("assets/img/dlr.png");
                return '<img src="' . $logo . '" height="40px">';
            })
            ->addColumn('coin_type', function ($item) {
                return $item?->coin?->coin_type ?? __("Not found");
            })
            ->filterColumn('coin_type', function ($query, $keyword) {
                $query->whereHas('coin', function ($q) use ($keyword) {
                    $q->where('coin_type', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('type', function ($item) {
                return CoinType::tryFrom($item->type)->getText();
            })
            ->addColumn('network_name', function ($item) {
                return $item?->network?->name ?? __("Not found");
            })
            ->filterColumn('network_name', function ($query, $keyword) {
                $query->whereHas('network', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('status', function ($item) {
                return '<label class="switch">
                            <input onchange="changeNetworkStatus(' . $item->id . ')" type="checkbox" name="status" ' . (($item->status == STATUS_ACTIVE) ? "checked" : "") . ' />
                            <span class="slider"></span>
                        </label>';
            })
            ->editColumn('created_at', function ($item) {
                return $this->dateFormat($item->created_at);
            })
            ->editColumn('updated_at', function ($item) {
                return $this->dateFormat($item->updated_at);
            })
            ->addColumn('actions', function ($item) {
                return CoinNetworkActionRenderer::render($item, $item?->coin, 'updateCoinNetwork');
            })
            ->rawColumns(['logo', 'status', 'actions'])
            ->make(true);
    }
}
