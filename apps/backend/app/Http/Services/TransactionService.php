<?php

namespace App\Http\Services;

use App\Dtos\Calculate24HourPriceDto;
use App\Http\Repositories\TransactionRepository;
use App\Model\CoinPair;
use App\Model\OneDay;
use App\Model\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionService extends CommonService
{
    public TransactionRepository $object;
    public $model = Transaction::class;
    public $repository = TransactionRepository::class;

    public function __construct()
    {
        parent::__construct($this->model, $this->repository);
    }

    public function getOrders()
    {
        return $this->object->getOrders();
    }

    public function getOrdersQuery()
    {
        return $this->object->getOrdersQuery();
    }
    public function getOrdersQueryReport($type)
    {
        return $this->object->getOrdersQueryReport($type);
    }

    public function getTradeHistoryForUsers($baseCoinId, $tradeCoinId, $userId, $orderType = null, $duration = null)
    {
        $select = ['transaction_id', DB::raw("CASE WHEN buy_user_id =" . $userId . " THEN buy_fees WHEN sell_user_id =" . $userId . " THEN sell_fees END as fees"), DB::raw("visualNumberFormat(amount) as amount"), DB::raw("visualNumberFormat(price) as price"), DB::raw("visualNumberFormat(last_price) as last_price"), 'price_order_type', DB::raw("visualNumberFormat(total) as total"), 'created_at', DB::raw("TIME(created_at) as time")];
        $where = [
            'base_coin_id' => $baseCoinId,
            'trade_coin_id' => $tradeCoinId,
        ];
        $time = 0;
        $orWhere = [];


        if (empty($orderType)) {
            $where['buy_user_id'] = $userId;
            $orWhere = [
                'sell_user_id' => $userId,
                'base_coin_id' => $baseCoinId,
                'trade_coin_id' => $tradeCoinId,
            ];
        } else {
            if ($orderType == 'buy') {
                $where['buy_user_id'] = $userId;
                $select[] = 'buy_fees as fees';
            } else {
                $where['sell_user_id'] = $userId;
                $select[] = 'sell_fees as fees';
            }
        }

        if (!empty($duration) || ($duration != 0)) {
            $time = Carbon::now()->subDays($duration);
        }

        return $this->object->getMyTradeHistory($select, $where, $orWhere, $time)->limit(20)->get();
    }

    public function getMyTradeHistory($baseCoinId, $tradeCoinId, $userId, $orderType = null, $duration = null)
    {
        $select = ['transaction_id', DB::raw("CASE WHEN buy_user_id =" . getUserId() . " THEN buy_fees WHEN sell_user_id =" . getUserId() . " THEN sell_fees END as fees"), DB::raw("visualNumberFormat(amount) as amount"), DB::raw("visualNumberFormat(price) as price"), DB::raw("visualNumberFormat(last_price) as last_price"), 'price_order_type', DB::raw("visualNumberFormat(total) as total"), 'created_at', DB::raw("TIME(created_at) as time")];
        $where = [
            'base_coin_id' => $baseCoinId,
            'trade_coin_id' => $tradeCoinId,
        ];
        $time = 0;
        $orWhere = [];
        if (Auth::check()) {

            if (empty($orderType)) {
                $where['buy_user_id'] = $userId;
                $orWhere = [
                    'sell_user_id' => $userId,
                    'base_coin_id' => $baseCoinId,
                    'trade_coin_id' => $tradeCoinId,
                ];
            } else {
                if ($orderType == 'buy') {
                    $where['buy_user_id'] = $userId;
                    $select[] = 'buy_fees as fees';
                } else {
                    $where['sell_user_id'] = $userId;
                    $select[] = 'sell_fees as fees';
                }
            }
        } else {
            if (empty($orderType)) {
                $where['buy_user_id'] = 0;
                $orWhere = [
                    'sell_user_id' => 0,
                    'base_coin_id' => $baseCoinId,
                    'trade_coin_id' => $tradeCoinId,
                ];
            } else {
                if ($orderType == 'buy') {
                    $where['buy_user_id'] = 0;
                    $select[] = 'buy_fees as fees';
                } else {
                    $where['sell_user_id'] = 0;
                    $select[] = 'sell_fees as fees';
                }
            }
        }

        if (!empty($duration) || ($duration != 0)) {
            $time = Carbon::now()->subDays($duration);
        }

        return $this->object->getMyTradeHistory($select, $where, $orWhere, $time);
    }

    public function getMyAllTransactionHistory($userId, $order_data)
    {
        $select = [
            'transaction_id',
            DB::raw("CASE WHEN buy_user_id =" . $userId . " THEN buy_fees WHEN sell_user_id =" . $userId . " THEN sell_fees END as fees"),
            DB::raw("visualNumberFormat(amount) as amount"),
            DB::raw("bc.coin_type as base_coin"),
            DB::raw("tc.coin_type as trade_coin"),
            DB::raw("visualNumberFormat(price) as price"),
            DB::raw("visualNumberFormat(last_price) as last_price"),
            'price_order_type',
            DB::raw("visualNumberFormat(total) as total"),
            DB::raw("transactions.created_at as time")
        ];
        $where['transactions.buy_user_id'] = $userId;
        $orWhere['transactions.sell_user_id'] = $userId;

        return $this->object->getMyAllTradeHistory($select, $where, $orWhere, $order_data);
    }

    public function getAllTradeHistory($baseCoinId, $tradeCoinId)
    {
        $where = [
            'base_coin_id' => $baseCoinId,
            'trade_coin_id' => $tradeCoinId,
        ];

        return $this->object->getAllTradeHistory($where);
    }

    public function getLastTradeHistory($baseCoinId, $tradeCoinId)
    {
        $where = [
            'base_coin_id' => $baseCoinId,
            'trade_coin_id' => $tradeCoinId,
        ];

        return $this->object->getLastTrade($where);
    }

    public function getLastTradeForExchange($baseCoinId, $tradeCoinId)
    {
        $where = [
            'base_coin_id' => $baseCoinId,
            'trade_coin_id' => $tradeCoinId,
        ];

        return $this->object->getLastTradeHistory($where);
    }

    /**
     * Get 24 Hours Change
     */
    public static function calculate24HourPrice(Calculate24HourPriceDto $pair, ?Transaction $transaction = null)
    {
        $referencePrice = app('spot_chart_cache')->getLast24ClosePrice($pair->child_coin_id, $pair->parent_coin_id);

        if (!$referencePrice)
            return 0;

        if (empty($transaction)) {
            $dataObject = [
                'base_coin_id' => $pair->parent_coin_id,
                'trade_coin_id' => $pair->child_coin_id
            ];

            $transactionResponse = app('spot_transaction_cache')->getLastTransaction((object) $dataObject)['data'];
            if (!$transactionResponse) {
                $transaction = Transaction::where(['base_coin_id' => $pair->parent_coin_id, 'trade_coin_id' => $pair->child_coin_id])
                    ->latest()->first();
            }

            $transaction ??= (object) $transactionResponse;
        }

        if (
            empty($transaction)  ||
            $referencePrice == 0 ||
            (!isset($transaction->price) || !is_numeric($transaction->price) || $transaction->price == 0)
        ) {
            return 0;
        }

        $change = bcmulx(bcdivx(bcsubx($transaction->price, $referencePrice), $referencePrice), 100);

        return $change;
    }

    /**
     * Get 24 Hours Change Data
     */
    public static function calculate24HourData(Calculate24HourPriceDto $pairDto, ?Transaction $transaction = null): array
    {
        $return24HourData = [
            'change' => "0",
            'high' => "0",
            'low' => "0",
            'volume' => "0"
        ];

        if (empty($transaction)) {
            $dataObject = [
                'base_coin_id' => $pairDto->parent_coin_id,
                'trade_coin_id' => $pairDto->child_coin_id
            ];

            $transactionResponse = app('spot_transaction_cache')->getLastTransaction((object) $dataObject)['data'];
            if (!$transactionResponse) {
                $transaction = Transaction::where(['base_coin_id' => $pairDto->parent_coin_id, 'trade_coin_id' => $pairDto->child_coin_id])
                    ->latest()->first();
            }

            $transaction ??= (object) $transactionResponse;
        }

        if (empty($transaction))
            return $return24HourData;

        $transaction24HourData = Transaction::select(
            DB::raw('max(price) as max'),
            DB::raw('min(price) as min'),
            DB::raw('sum(amount) as total'),
            DB::raw('sum(price * amount) as base_total')
        )
            ->where(['base_coin_id' => $transaction->base_coin_id, 'trade_coin_id' => $transaction->trade_coin_id])
            ->where('created_at', '>', Carbon::now()->subDays(1))
            ->groupBy(['base_coin_id', 'trade_coin_id'])
            ->first();

        $transactionObject = $transaction instanceof Transaction ? $transaction : new Transaction((array) $transaction);

        $price24hChange = self::calculate24HourPrice($pairDto, $transactionObject);
        if (!$price24hChange)
            $price24hChange = CoinPair::where([
                'parent_coin_id' => $pairDto->parent_coin_id,
                'child_coin_id' => $pairDto->child_coin_id
            ])->first()?->change ?? "0";

        $return24HourData['change'] = (string) $price24hChange;
        $return24HourData['high'] = (string) $transaction24HourData?->max ?? 0;
        $return24HourData['low'] = (string) $transaction24HourData?->min ?? 0;
        $return24HourData['volume'] = (string) $transaction24HourData?->total ?? 0;

        return $return24HourData;
    }
}
