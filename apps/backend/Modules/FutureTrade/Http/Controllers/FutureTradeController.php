<?php

namespace Modules\FutureTrade\Http\Controllers;

use App\Facades\ResponseFacade;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Entities\FutureTrade;
use Modules\FutureTrade\Services\TradeServices\TradeService;

class FutureTradeController extends Controller
{
    public function list(): mixed
    {
        if (IS_API_CALL) {
            $query = FutureTrade::query()
                ->join('future_coin_pairs', 'future_coin_pairs.id', '=', 'future_trades.future_coin_pair_id')
                ->join('users as buyer', 'buyer.id', '=', 'future_trades.buyer_id')
                ->join('users as seller', 'seller.id', '=', 'future_trades.seller_id')
                ->select(
                    'future_trades.*',
                    'buyer.email as buyer_email',
                    'seller.email as seller_email',
                    'future_coin_pairs.code',
                    'future_coin_pairs.trade_decimal',
                    'future_coin_pairs.base_coin_code',
                    'future_coin_pairs.trade_coin_code'
                );
            return datatables($query)
                ->addColumn('code', fn($trade) => $trade?->code ?? 'N/A')
                ->filterColumn('code', function ($query, $keyword) {
                    $query->where('future_coin_pairs.code', 'LIKE', "%$keyword%");
                })
                ->filterColumn('uid', function ($query, $keyword) {
                    $query->where('future_trades.uid', 'LIKE', "%$keyword%");
                })
                ->addColumn('buyer', fn($trade) => $trade?->buyer_email ?? 'N/A')
                ->filterColumn('buyer', function ($query, $keyword) {
                    $query->where('buyer.email', 'LIKE', "%$keyword%");
                })
                ->addColumn('seller', fn($trade) => $trade?->seller_email ?? 'N/A')
                ->filterColumn('seller', function ($query, $keyword) {
                    $query->where('seller.email', 'LIKE', "%$keyword%");
                })
                ->addColumn('price', fn($trade) => trim_num($trade->price, $trade->trade_decimal ?: 8) . ' ' . $trade->trade_coin_code ?? '')
                ->filterColumn('price', function ($query, $keyword) {
                    $query->where('future_trades.price', 'LIKE', "%$keyword%");
                })
                ->addColumn('amount', fn($trade) => trim_num($trade->amount, $trade->trade_decimal ?: 8) . ' ' . $trade->base_coin_code ?? '')
                ->filterColumn('amount', function ($query, $keyword) {
                    $query->where('future_trades.amount', 'LIKE', "%$keyword%");
                })
                ->addColumn(
                    'total',
                    fn($trade) =>
                    trim_num(
                        bcmulx(
                            $trade->price,
                            $trade->amount,
                            $trade->trade_decimal ?? 8
                        ),
                        $trade->trade_decimal ?: 8
                    ) . ' ' . $trade->trade_coin_code ?? ''
                )
                ->editColumn('created_at', '{{ date("d-m-Y H:i a", strtotime($created_at)) }}')
                ->editColumn('action', 'futureTrade::trades.components.action')
                ->rawColumns(['base_coin_code', 'trade_coin_code', 'status', 'action'])
                ->make(true);
        }
        $data['title'] = __('Future Trades');
        return view('futureTrade::trades.list', $data);
    }

    public function getTrade(Request $req, TradeService $service)
    {
        if (!$req->filled('uid')) {
            return ResponseFacade::failed(__('Trade uid is required'))->send();
        }

        $order = FutureTrade::where(['future_trades.uid' => $req->uid])
            ->join('future_coin_pairs', 'future_coin_pairs.id', '=', 'future_trades.future_coin_pair_id')
            ->join('users as buyer', 'buyer.id', '=', 'future_trades.buyer_id')
            ->join('users as seller', 'seller.id', '=', 'future_trades.seller_id')
            ->select(
                'future_trades.*',
                'buyer.id as buy_user_id',
                'buyer.email as buyer_email',
                'seller.id as sell_user_id',
                'seller.email as seller_email',
                'future_coin_pairs.code',
                'future_coin_pairs.trade_decimal',
                'future_coin_pairs.base_coin_code',
                'future_coin_pairs.trade_coin_code'
            )->first();

        $orderHtmlData = view('futureTrade::trades.components.trade-details', ['trade' => $order])->render();

        return ResponseFacade::success(['html' => $orderHtmlData])->send();
    }
}
