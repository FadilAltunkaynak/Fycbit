<?php

namespace App\Http\Services\DataTable;

use App\Enums\WalletAddressStatus;
use App\Model\WalletAddressHistory;
use App\Traits\DateFormatTrait;
use Illuminate\Support\Facades\DB;

class WalletAddressDataTableService
{
    use DateFormatTrait;

    public function getData()
    {
        $address_list = DB::table('wallet_address_histories')
            ->join('wallets', 'wallets.id', '=', 'wallet_address_histories.wallet_id')
            ->join('users', 'users.id', '=', 'wallets.user_id')
            ->join('coins', 'coins.id', '=', 'wallets.coin_id')
            ->join('networks', 'networks.id', '=', 'wallet_address_histories.network_id')
            ->select(
                'wallet_address_histories.wallet_id',
                'wallet_address_histories.address',
                'wallet_address_histories.status',
                'wallet_address_histories.created_at',
                'users.email as user_email',
                'coins.coin_type as coin_type',
                "networks.name as network_name"
            );

        $address_network_list = DB::table('wallet_networks')
            ->join('wallets', 'wallets.id', '=', 'wallet_networks.wallet_id')
            ->join('users', 'users.id', '=', 'wallets.user_id')
            ->join('coins', 'coins.id', '=', 'wallets.coin_id')
            ->select(
                'wallet_networks.wallet_id',
                'wallet_networks.address',
                'wallet_networks.status',
                'wallet_networks.created_at',
                'users.email as user_email',
                'coins.coin_type as coin_type',
                "wallet_networks.network_type as network_name"
            );

        $combined_query = $address_list->union($address_network_list);
        $data = DB::table(DB::raw("({$combined_query->toSql()}) as combined"))
            ->mergeBindings($combined_query)
            ->orderByDesc('created_at');

        return datatables()->of($data)
            ->editColumn('status', function ($item) {
                return WalletAddressStatus::tryFrom($item->status)->statusHtml();
            })
            ->editColumn('created_at', function ($item) {
                return $this->dateFormat($item->created_at);
            })
            ->editColumn('network_name', function ($item) {
                return $item->network_name ?? __("Not found");
            })
            ->filterColumn('user_email', function ($query, $keyword) {
                $query->where('user_email', 'LIKE', "%$keyword%");
            })
            ->filterColumn('coin_type', function ($query, $keyword) {
                $query->where('coin_type', 'LIKE', "%$keyword%");
            })
            ->filterColumn('status', function ($query, $keyword) {
                $keyword = strtolower($keyword);
                $matchingCodes = array_keys(array_filter(WalletAddressStatus::getList(), function ($label) use ($keyword) {
                    return strpos(strtolower($label), $keyword) !== false;
                }));
                if (!empty($matchingCodes)) {
                    $query->whereIn('status', $matchingCodes);
                }
            })
            ->rawColumns(['status'])
            ->make(true);
    }
}
