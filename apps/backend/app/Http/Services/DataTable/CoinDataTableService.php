<?php

namespace App\Http\Services\DataTable;

use App\Enums\CoinProvider;
use App\Model\Coin;
use App\Traits\DateFormatTrait;
use App\Traits\NumberFormatTrait;

class CoinDataTableService
{
    use NumberFormatTrait, DateFormatTrait;

    public function getData($check_module = [])
    {
        if (@$check_module['IcoLaunchpad'] == 'IcoLaunchpad') {
            $coins = Coin::query()->where(function ($query) {
                return $query->where('status', '<>', STATUS_DELETED);
            });
        } else {
            $coins = Coin::where('status', '<>', STATUS_DELETED)->where(function ($query) {
                return $query->where('ico_id', '=', 0)->orWhere('is_listed', STATUS_ACTIVE);
            });
        }

        return datatables()->of($coins)
            ->editColumn('created_at', function ($item) {
                return $this->dateFormat($item->created_at);
            })
            ->editColumn('currency_type', function ($item) {
                return getTradeCurrencyType($item->currency_type);
            })
            ->editColumn('active_provider', function ($item) {
                return CoinProvider::tryFrom($item->active_provider)?->getText();
            })
            ->editColumn('coin_price', function ($item) {
                return "<span>" . $this->truncateNum($item->coin_price) . "</br> USD/" . $item->coin_type . "</span>";
            })
            ->editColumn('status', function ($item) {
                $data['coin'] = $item;
                return view('admin.coin-order.switch.ico_switch', $data);
            })
            ->editColumn('is_demo_trade', function ($item) {
                $data['coin'] = $item;
                return view('admin.coin-order.switch.demo_switch', $data);
            })
            ->addColumn('actions', function ($item) {
                $data['coin'] = $item;
                return view('admin.coin-order.switch.evm_actions', $data);
            })
            ->rawColumns(['coin_price', 'status', 'is_demo_trade', 'actions'])
            ->make(true);
    }
}
