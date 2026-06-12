<?php

namespace Modules\P2P\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Modules\P2P\Entities\PBuy;
use Modules\P2P\Entities\PSell;
use Illuminate\Routing\Controller;

class AdsController extends Controller
{
    public function __construct()
    {

    }

    public function adsListPage(Request $request)
    {
        $data = [];
        $data['title'] = __("Advertisement List");
        try {
            $data['tab'] = $request->tab ?? 'buy';
        } catch (\Exception $e) {
            storeException("adsListPage", $e->getMessage());
        }
        return view("p2p::ads.ads", $data);
    }

    public function adsBuyList(Request $request)
    {
        try {
            if ( $request->ajax() ) {
                $buys = PBuy::with("user")->get();
                return $this->getTableData($buys);
            }
            return response()->json(responseData(false, __("Something went wrong")));
        } catch (\Exception $e) {
            storeException("adsBuyList", $e->getMessage());
            return response()->json(responseData(false, __("Something went wrong")));
        }
    }

    public function adsSellList(Request $request)
    {
        try {
            if ( $request->ajax() ) {
                $sells = PSell::with("user")->get();
                return $this->getTableData($sells);
            }
            return response()->json(responseData(false, __("Something went wrong")));
        } catch (\Exception $e) {
            storeException("adsBuyList", $e->getMessage());
            return response()->json(responseData(false, __("Something went wrong")));
        }
    }

    private function getTableData($items){
        try {
            return datatables($items)
            ->addColumn('user', function ($item) {
                $user = $item->user()->first();
                return $user->first_name.' '.$user->last_name;
            })
            ->addColumn('coin', function ($item) {
                return $item->coin_type;
            })
            ->addColumn('amount', function ($item) {
                return $item->amount . ' ' .$item->coin_type;
            })
            ->addColumn('coin_rate', function ($item) {
                return $item->price . ' ' .$item->currency;
            })
            ->addColumn('available', function ($item) {
                return $item->available . ' ' .$item->coin_type;
            })
            ->editColumn('created_at', function ($item) {
                return $item->created_at ? with(new Carbon($item->created_at))->format('d M Y') : '';
            })
            // ->rawColumns(['activity', 'status','online_status'])
            ->make(true);
        } catch (\Exception $e) {
            storeException("getTableData", $e->getMessage());
            return responseData(false, __("Failed"));
        }
    }
}
