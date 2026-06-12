<?php

namespace Modules\FutureTrade\Services\TradeServices;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\FutureTrade\DataObject\Calculate24HourPriceDto;
use Modules\FutureTrade\DataObject\FutureTradeData;
use Modules\FutureTrade\Entities\Charts\FutureOneDayChart;
use Modules\FutureTrade\Entities\FutureCoinPair;
use Modules\FutureTrade\Entities\FutureTrade;
use Modules\FutureTrade\Http\Requests\Api\FutureTradeHistoryFilterRequest;
use Modules\FutureTrade\Repositories\TradeRepository\IFutureTradeRepository;
use Modules\FutureTrade\Transformers\MarketTradeList;
use Modules\FutureTrade\Emum\TakerMakerEnum;

class TradeService
{
    public IFutureTradeRepository $repository;

    public function __construct(IFutureTradeRepository $repository)
    {
        $this->repository = $repository;
    }
    public function createNewTrade(FutureTradeData $tradeData): FutureTrade
    {
        return $this->repository->createTrade($tradeData);
    }

    public function marketTradeList(string $coin_pair_uid)
    {
        $coinPair = FutureCoinPair::where('uid', $coin_pair_uid)->first();

        $trades = FutureTrade::with('coin_pair')->where([
            'future_coin_pair_id' => $coinPair?->id ?? 0
        ])->latest()->limit(100)->get();

        return MarketTradeList::collection($trades);
    }

    public function tradeHistoryFilter(FutureTradeHistoryFilterRequest $request)
    {
        $paginator = $this->repository->tradeHistoryFilter($request, authId());
        $items = $paginator->getCollection()->map(function ($trade) {
            return [
                'uid' => $trade->uid,
                'side' => $trade->side,
                'price' => trim_num($trade->price, $trade->trade_decimal ?: 8),
                'amount' => trim_num($trade->amount, $trade->trade_decimal ?: 8),
                'total_price' => trim_num($trade->total_price, $trade->trade_decimal ?: 8),
                'taker_fees' => trim_num($trade->taker_fees, $trade->trade_decimal ?: 8),
                'maker_fees' => trim_num($trade->maker_fees, $trade->trade_decimal ?: 8),
                'created_at' => $trade->created_at,
                'code' => $trade->code,
                'trade_coin_code' => $trade->trade_coin_code,
                'base_coin_code' => $trade->base_coin_code,
                'role' => $trade->taker_id == authId()
                    ? TakerMakerEnum::TAKER->value
                    : TakerMakerEnum::MAKER->value,
                'pnl' => 0,
            ];
        })->values();

        $paginator->setCollection($items);

        return $paginator;
    }

    /**
     * Get 24 Hours Change Data
     */
    public static function calculate24HourData(Calculate24HourPriceDto $pairDto, ?FutureTrade $transaction = null): array
    {
        $return24HourData = [
            'change' => "0",
            'high'   => "0",
            'low'    => "0",
            'volume' => "0"
        ];

        if (empty($transaction))
            $transaction = FutureTrade::where([
                'base_coin_id'  => $pairDto->base_coin_id,
                'trade_coin_id' => $pairDto->trade_coin_id
            ])->latest()->first();

        if (empty($transaction)) return $return24HourData;

        $transaction24HourData = FutureTrade::select(
            DB::raw('max(price) as max'),
            DB::raw('min(price) as min'),
            DB::raw('sum(amount) as total')
        )
            ->where(['base_coin_id' => $transaction->base_coin_id, 'trade_coin_id' => $transaction->trade_coin_id])
            ->where('created_at', '>', Carbon::now()->subDays(1))
            ->groupBy(['base_coin_id', 'trade_coin_id'])
            ->first();

        $price24hChange = self::calculate24HourPrice($pairDto, $transaction);
        if(!$price24hChange) $price24hChange = FutureCoinPair::where([
                'base_coin_id' => $pairDto->base_coin_id,
                'trade_coin_id'  => $pairDto->trade_coin_id
        ])->first()?->change ?? "0";

        $return24HourData['change'] = (string) $price24hChange;
        $return24HourData['high']   = (string) $transaction24HourData?->max ?? 0;
        $return24HourData['low']    = (string) $transaction24HourData?->min ?? 0;
        $return24HourData['volume'] = (string) $transaction24HourData?->total ?? 0;

        return $return24HourData;
    }

    public static function calculate24HourPrice(Calculate24HourPriceDto $pair, ?FutureTrade $transaction = null)
    {
        $yesterday  = Carbon::yesterday();
        $price24Ago = FutureOneDayChart::select("close")->where([
            'base_coin_id'  => $pair->base_coin_id,
            'trade_coin_id' => $pair->trade_coin_id
        ])->whereDate("created_at", $yesterday)->latest()->first();

        if(!$referencePrice = $price24Ago?->close)
        return 0;

        if (empty($transaction))
            $transaction = FutureTrade::where(['base_coin_id' => $pair->base_coin_id, 'trade_coin_id' => $pair->trade_coin_id])
                ->latest()->first();

        if (empty($transaction) || $referencePrice == 0)
            return  0;

        $change = bcmulx(bcdivx(bcsubx($transaction->price, $referencePrice), $referencePrice), 100);

        return $change;
    }
}
