<?php

namespace Modules\DemoTrade\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\OrderHistory;
use App\Exports\BuyOrderHistory;
use App\Exports\TradeTransaction;
use App\Model\TradeReferralHistory;
use Modules\DemoTrade\Entities\Buy;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Modules\DemoTrade\Entities\Sell;
use Modules\DemoTrade\Entities\StopLimit;
use App\Http\Services\TradeReferralService;
use Modules\DemoTrade\Entities\Transaction;
use Modules\DemoTrade\Http\Services\BuyOrderService;
use App\Http\Requests\Admin\TransactionExportRequest;
use Modules\DemoTrade\Http\Services\SellOrderService;
use Modules\DemoTrade\Http\Services\StopLimitService;
use Modules\DemoTrade\Http\Services\TransactionService;

class ReportController extends Controller
{
    /*
  *
  * All Stop Limit Orders History
  * adminAllOrdersHistoryStopLimit
  *
  * Show the list of specified resource.
  * @return \Illuminate\Http\Response
  *
  */
    public function adminAllOrdersHistoryStopLimit(Request $request)
    {
        $data['title'] = __('Stop Limit Order History');
        $service = new StopLimitService();
        $data['type'] = 'stop_limit';
        $data['sub_menu'] = 'stop_limit';

        if ($request->ajax()) {

            $data['items'] = $service->getOrders();

            return datatables($data['items'])
                ->addColumn('order_type', function ($item) {
                    return ucfirst($item->order_type);
                })
                ->make(true);
        }

        return view('demotrade::report.stop_limit_order_report',$data);
    }
  /*
  *
  * All Buy Orders History
  * adminAllOrdersHistoryBuy
  *
  * Show the list of specified resource.
  * @return \Illuminate\Http\Response
  *
  */
    public function adminAllOrdersHistoryBuy(Request $request)
    {
        $data['title'] = __('Buy Order History');
        $buyService = new BuyOrderService();
        $data['type'] = 'buy';
        $data['sub_menu'] = 'buy_order';

        if ($request->ajax()) {

            $data['items'] = $buyService->getOrders();

            return datatables($data['items'])
                ->editColumn('is_market', function ($item) {
                    return $item->is_market ? 'Market' : 'Normal';
                })
                ->editColumn('status', function ($item) {
                    if($item->status == 1) {
                        return __('Success');
                    } elseif($item->deleted_at != null) {
                        return __('Processing');
                    } elseif($item->status == 0) {
                        return __('Pending');
                    } else {
                        return __('Deleted');
                    }
                })
                ->make(true);
        }

        return view('demotrade::report.buy_order_report',$data);
    }

    /*
   *
   * All Sell Orders History
   * adminAllOrdersHistorySell
   *
   * Show the list of specified resource.
   * @return \Illuminate\Http\Response
   *
   */
    public function adminAllOrdersHistorySell(Request $request)
    {
        $data['title'] = __('Sell Order History');
        $data['type'] = 'sell';
        $data['sub_menu'] = 'sell_order';
        $sellService = new SellOrderService();

        if ($request->ajax()) {
            $data['items'] = $sellService->getOrders();

            return datatables($data['items'])
                ->editColumn('is_market', function ($item) {
                    return $item->is_market ? 'Market' : 'Normal';
                })
                ->editColumn('status', function ($item) {
                    if($item->status == 1) {
                        return __('Success');
                    } elseif($item->deleted_at != null) {
                        return __('Processing');
                    } elseif($item->status == 0) {
                        return __('Pending');
                    } else {
                        return __('Deleted');
                    }
                })
                ->make(true);
        }

        return view('demotrade::report.sell_order_report',$data);
    }

    /*
   *
   * All Sell buy transaction Orders History
   * adminAllTransactionHistory
   *
   * Show the list of specified resource.
   * @return \Illuminate\Http\Response
   *
   */
    public function adminAllTransactionHistory(Request $request)
    {
        $data['title'] = __('Transaction History');
        $data['sub_menu'] = 'transaction';
        $sellService = new TransactionService();

        if ($request->ajax()) {
            $data['items'] = $sellService->getOrders();

            return datatables($data['items'])
                ->make(true);
        }

        return view('demotrade::report.transaction_report',$data);
    }

    public function adminClearAllHistory($type)
    {
        if($type == 'buy') {
            Buy::truncate();
        }
        else if($type == 'sell') {
            Sell::truncate();
        }
        else if($type == 'stop') {
            StopLimit::truncate();
        }
        else if($type == 'transaction') {
            Transaction::truncate();
        }
        return redirect()->back()->with('success', __("Cleard History"));
    }
}
