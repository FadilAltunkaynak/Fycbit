<?php

namespace Modules\FutureTrade\Repositories\TradeRepository;

use App\Http\Repositories\CommonRepository;
use App\Model\Coin;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\FutureTrade\DataObject\FutureTradeData;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Emum\SortEnum;
use Modules\FutureTrade\Entities\FutureTrade;
use Modules\FutureTrade\Http\Requests\Api\FutureTradeHistoryFilterRequest;
use Modules\FutureTrade\Repositories\TradeRepository\IFutureTradeRepository;

class FutureTradeRepository extends CommonRepository implements IFutureTradeRepository
{
    /**
     * Constructor
     *
     * @param  FutureTrade|null  $model
     */
    public function __construct($model = null)
    {
        parent::__construct($model ?: app(FutureTrade::class));
    }

    /**
     * Create New Future Trade
     */
    public static function createTrade(FutureTradeData $tradeData): FutureTrade
    {
        return FutureTrade::create($tradeData->toArray());
    }

    /**
     * Get Future Trade By User ID
     */
    public static function getLastTrade(int $coin_id): ?FutureTrade
    {
        return FutureTrade::where([
            'future_coin_pair_id' => $coin_id,
        ])->latest()->first();
    }

    /**
     * Lock Balance to in order balance
     * recommended to use DB::beginTransaction() lockForUpdate used
     * use rescue() to avoid exception
     */
    public function lockTradeBalance(Coin $coin, float|string $balance, ?int $user_id = null): bool
    {
        $wallet = \Modules\FutureTrade\Entities\FutureWallet::where('user_id', $user_id ?: authId())
            ->where('coin_id', $coin->id)
            ->lockForUpdate()->first();

        if (!$wallet)
            return false;

        $status = $wallet->decrement('available_balance', $balance) &&
            $wallet->increment('in_order_balance', $balance);

        return $status;
    }

    /**
     * Release Balance from in order balance on cancel
     * recommended to use DB::beginTransaction() lockForUpdate used
     * use rescue() to avoid exception
     */
    public function releaseTradeBalance(Coin $coin, float|string $balance, ?int $user_id = null): bool
    {
        $wallet = \Modules\FutureTrade\Entities\FutureWallet::where('user_id', $user_id ?: authId())
            ->where('coin_id', $coin->id)
            ->lockForUpdate()->first();

        if (!$wallet)
            return false;

        $status = $wallet->decrement('in_order_balance', $balance) &&
            $wallet->increment('available_balance', $balance);

        return $status;
    }

    public function tradeHistoryFilter(FutureTradeHistoryFilterRequest $request, int $user_id): LengthAwarePaginator
    {
        $query = FutureTrade::query()
            ->select([
                'future_trades.id',
                'future_trades.uid',
                'future_trades.future_coin_pair_id',
                'future_trades.order_type',
                'future_trades.price',
                'future_trades.amount',
                'future_trades.total_price',
                'future_trades.taker_id',
                'future_trades.taker_fees',
                'future_trades.maker_fees',
                'future_trades.created_at',
                'future_trades.buyer_id',
                'future_trades.seller_id',
                'future_coin_pairs.code',
                'future_coin_pairs.trade_coin_code',
                'future_coin_pairs.base_coin_code',
                'future_coin_pairs.trade_decimal',
            ])
            ->join('future_coin_pairs', 'future_coin_pairs.id', '=', 'future_trades.future_coin_pair_id');

        if ($request->filled('symbol')) {
            $query->where('future_coin_pairs.code', $request->symbol);
        }

        $orderType = OrderType::tryFrom($request->side);
        if ($orderType === OrderType::BUY) {
            $query->where('future_trades.buyer_id', $user_id);
        } elseif ($orderType === OrderType::SELL) {
            $query->where('future_trades.seller_id', $user_id);
        } else {
            $query->where(function ($q) use ($user_id) {
                $q->where('future_trades.buyer_id', $user_id)
                    ->orWhere('future_trades.seller_id', $user_id);
            });
        }

        if ($request->filled('time')) {
            $time_to = now();
            $time_from = $time_to->copy()->subDays($request->time);
            $query->whereBetween('future_trades.created_at', [$time_from->toDateTimeString(), $time_to->toDateTimeString()]);
        }

        if ($request->filled('time_from')) {
            $time_to = Carbon::parse($request->input('time_to'));
            $time_from = Carbon::parse($request->input('time_from'));
            $query->whereBetween('future_trades.created_at', [$time_from->toDateTimeString(), $time_to->toDateTimeString()]);
        }

        $sort = SortEnum::tryFrom($request->sort) ?? SortEnum::DESC;
        $query->orderBy('future_trades.created_at', $sort->label());

        $paginated = $query->paginate($request->limit ?? 20);

        foreach ($paginated as $trade) {
            /** @var FutureTrade $trade */
            $trade->side = $trade->buyer_id === $user_id ? OrderType::BUY->value : OrderType::SELL->value;
        }

        return $paginated;
    }

}
