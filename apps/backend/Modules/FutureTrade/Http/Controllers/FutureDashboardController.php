<?php

namespace Modules\FutureTrade\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\FutureTrade\Emum\PositionStatusEnum;
use Modules\FutureTrade\Entities\FutureBuy;
use Modules\FutureTrade\Entities\FuturePosition;
use Modules\FutureTrade\Entities\FutureSell;
use Modules\FutureTrade\Entities\FutureTrade;
use Modules\FutureTrade\Services\MathService\PositionMath;

class FutureDashboardController extends Controller
{
    public function dashboard()
    {
        $data['title'] = __('Future Trade Dashboard');
        $data['total_users'] = User::count();
        $data['total_buy_orders'] = FutureBuy::count();
        $data['total_sell_orders'] = FutureSell::count();
        $data['total_positions'] = FuturePosition::where('status', PositionStatusEnum::OPEN->value)->count();

        // Advanced KPIs
        $data['volume_24h'] = FutureTrade::where('created_at', '>=', now()->subDay())->sum('total_price');
        $data['fees_24h'] = FutureTrade::where('created_at', '>=', now()->subDay())->sum(DB::raw('taker_fees + maker_fees'));

        $openPositions = FuturePosition::with(['coinPair', 'user', 'leverageSetting', 'future_wallet'])
            ->where('status', PositionStatusEnum::OPEN->value)
            ->get();

        $data['open_interest'] = $openPositions->sum(function ($p) {
            return bcmulx(abs($p->amount), $p->price, 8);
        });

        // Risk Monitor
        $riskPositions = $openPositions->map(function ($p) {
            $decimal = $p->coinPair?->trade_decimal ?: 8;
            $markPrice = cache_service()->getMarkPrice($p->future_coin_pair_id);
            $p->pnl = PositionMath::getPnl(
                $markPrice,
                $p->price,
                abs($p->amount),
                $p->order_type,
                $decimal
            );
            return $p;
        })->take(5);

        $data['risk_monitor'] = $riskPositions;

        // Top Traders (24h Volume)
        $data['top_traders'] = FutureTrade::where('created_at', '>=', now()->subDay())
            ->select('buyer_id', DB::raw('sum(total_price) as volume'))
            ->groupBy('buyer_id')
            ->orderByDesc('volume')
            ->with('buyer')
            ->limit(5)
            ->get();

        // Monthly Trends
        $monthlyBuy = FutureBuy::select(
            DB::raw('count(id) as count'),
            DB::raw('MONTH(created_at) as month')
        )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $monthlySell = FutureSell::select(
            DB::raw('count(id) as count'),
            DB::raw('MONTH(created_at) as month')
        )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $data['monthly_buy'] = array_values(array_replace(array_fill(1, 12, 0), $monthlyBuy));
        $data['monthly_sell'] = array_values(array_replace(array_fill(1, 12, 0), $monthlySell));

        // Monthly Trade Volume
        $monthlyTradeVolume = FutureTrade::select(
            DB::raw('sum(total_price) as volume'),
            DB::raw('MONTH(created_at) as month')
        )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->pluck('volume', 'month')
            ->toArray();
        $data['monthly_trade_volume'] = array_values(array_replace(array_fill(1, 12, 0), $monthlyTradeVolume));

        // Monthly Position Count
        $monthlyPositionCount = FuturePosition::select(
            DB::raw('count(id) as count'),
            DB::raw('MONTH(created_at) as month')
        )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();
        $data['monthly_position_count'] = array_values(array_replace(array_fill(1, 12, 0), $monthlyPositionCount));

        // Recent Orders
        $buyOrders = FutureBuy::with(['coinPair', 'user'])
            ->latest()
            ->limit(5)
            ->get();

        $sellOrders = FutureSell::with(['coinPair', 'user'])
            ->latest()
            ->limit(5)
            ->get();

        $data['recent_orders'] = $buyOrders->concat($sellOrders)->sortByDesc('created_at')->take(5);

        // Active Positions
        $data['active_positions'] = FuturePosition::with(['coinPair', 'user'])
            ->where('status', PositionStatusEnum::OPEN->value)
            ->latest()
            ->limit(5)
            ->get();

        return view('futureTrade::dashboard.index', $data);
    }
}
