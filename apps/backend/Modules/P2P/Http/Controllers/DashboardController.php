<?php

namespace Modules\P2P\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\P2P\Entities\PBuy;
use Modules\P2P\Entities\POrder;
use Modules\P2P\Entities\PSell;
use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $data = [];
        try {
            $data['title'] = __("Dashboard");
            $data['buy']   = PBuy::get()->count();
            $data['active_buy']   = PBuy::where("status", STATUS_ACTIVE)->get()->count();
            $data['sell']  = PSell::get()->count();
            $data['active_sell']  = PSell::where("status", STATUS_ACTIVE)->get()->count();
            $data['trade'] = POrder::get()->count();
            $data['active_order'] = POrder::where(fn($query)=>
                                            $query->where("status", TRADE_STATUS_ESCROW)
                                            ->orWhere("status", TRADE_STATUS_PAYMENT_DONE)
                                            ->orWhere("status", TRADE_STATUS_PAYMENT_DONE)
                                    )->get()->count();
        } catch (\Exception $e) {
            storeException('p2p dashboard', $e->getMessage());
        }
        return view('p2p::dashboard',$data);
    }
}
