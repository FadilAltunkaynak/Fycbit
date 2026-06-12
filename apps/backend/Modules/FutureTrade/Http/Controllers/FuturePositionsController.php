<?php

namespace Modules\FutureTrade\Http\Controllers;

use App\Facades\ResponseFacade;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Emum\PositionStatusEnum;
use Modules\FutureTrade\Entities\FuturePosition;
use Modules\FutureTrade\Services\FuturePositionServices\FuturePositionService;

class FuturePositionsController extends Controller
{
    public function list(): mixed
    {
        if (IS_API_CALL) {
            $query = FuturePosition::query()
                ->join('future_coin_pairs', 'future_coin_pairs.id', '=', 'future_positions.future_coin_pair_id')
                ->join('users', 'users.id', '=', 'future_positions.user_id')
                ->where('future_positions.status', '<>', PositionStatusEnum::CLOSED->value)
                ->select(
                    'future_positions.*',
                    'users.email',
                    'future_coin_pairs.code',
                    'future_coin_pairs.trade_decimal',
                    'future_coin_pairs.base_coin_code',
                    'future_coin_pairs.trade_coin_code'
                );
            return datatables($query)
                ->addColumn('type', fn($trade) => $trade?->order_type?->bothLabelWithColor() ?? 'N/A')
                ->filterColumn('type', function ($query, $keyword) {
                    $status = match (true) {
                        str_contains(strtolower($keyword), 'long') => OrderType::BUY->value,
                        str_contains(strtolower($keyword), 'short') => OrderType::SELL->value,
                        str_contains(strtolower($keyword), 'buy') => OrderType::BUY->value,
                        str_contains(strtolower($keyword), 'sell') => OrderType::SELL->value,
                        default => 11
                    };
                    if ($status == 11)
                        return;
                    $query->where('future_positions.order_type', $status);
                })
                ->addColumn('code', fn($trade) => $trade?->code ?? 'N/A')
                ->filterColumn('code', function ($query, $keyword) {
                    $query->where('future_coin_pairs.code', 'LIKE', "%$keyword%");
                })
                ->filterColumn('uid', function ($query, $keyword) {
                    $query->where('future_positions.uid', 'LIKE', "%$keyword%");
                })
                ->addColumn('user', fn($trade) => $trade?->email ?? 'N/A')
                ->filterColumn('user', function ($query, $keyword) {
                    $query->where('users.email', 'LIKE', "%$keyword%");
                })
                ->addColumn('price', fn($trade) => trim_num($trade->price, $trade->trade_decimal ?: 8) . ' ' . $trade->trade_coin_code ?? '')
                ->filterColumn('price', function ($query, $keyword) {
                    $query->where('future_positions.price', 'LIKE', "%$keyword%");
                })
                ->addColumn('amount', fn($trade) => trim_num($trade->amount, $trade->trade_decimal ?: 8) . ' ' . $trade->base_coin_code ?? '')
                ->filterColumn('amount', function ($query, $keyword) {
                    $query->where('future_positions.amount', 'LIKE', "%$keyword%");
                })
                ->editColumn('status', fn($item) => $item->status->labelWithColor())
                ->editColumn('updated_at', '{{ date("d-m-Y H:i a", strtotime($created_at)) }}')
                ->editColumn('action', 'futureTrade::positions.components.action')
                ->rawColumns(['type', 'status', 'action'])
                ->make(true);
        }
        $data['title'] = __('Future Positions');
        return view('futureTrade::positions.list', $data);
    }

    public function getPositions(Request $req, FuturePositionService $service)
    {
        if (!$req->filled('uid')) {
            return ResponseFacade::failed(__('Trade uid is required'))->send();
        }

        $order = FuturePosition::where(['future_positions.uid' => $req->uid])
            ->join('future_coin_pairs', 'future_coin_pairs.id', '=', 'future_positions.future_coin_pair_id')
            ->join('users', 'users.id', '=', 'future_positions.user_id')
            ->select(
                'future_positions.*',
                'users.email',
                'future_coin_pairs.code',
                'future_coin_pairs.trade_decimal',
                'future_coin_pairs.base_coin_code',
                'future_coin_pairs.trade_coin_code'
            )->first();

        $orderHtmlData = view('futureTrade::positions.components.position-details', ['position' => $order])->render();

        return ResponseFacade::success(['html' => $orderHtmlData])->send();
    }
}
