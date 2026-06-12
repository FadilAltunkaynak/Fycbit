<?php

namespace Modules\DemoTrade\Http\Repositories;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\DemoTrade\Entities\Sell;
use App\Http\Repositories\CommonRepository;


class SellOrderRepository extends CommonRepository
{
    function __construct($model) {
        parent::__construct($model);
    }

    public function getOrders()
    {
        $db = env('DB_DATABASE', 'forge').'.';
        return Sell::select(DB::connection("DemoTradeMysql")->raw($db."users.email as email, base_coin_table.coin_type as base_coin, trade_coin_table.coin_type as trade_coin, price,
                                                amount,processed,sells.status,visualNumberFormat(TRUNCATE((amount - processed), 8)) as remaining,
                                                is_market, sells.created_at,sells.deleted_at"))
            ->join($db.'users',[$db.'users.id' => 'sells.user_id'])
            ->join($db.'coins as base_coin_table',['base_coin_id' => 'base_coin_table.id'])
            ->join($db.'coins as trade_coin_table',['trade_coin_id' => 'trade_coin_table.id'])
            ->withTrashed();
    }


    public function getAllOrders($base_coin_id, $trade_coin_id)
    {
        if (true || (env('APP_ENV') == 'local') || (env('APP_ENV') == 'dev') || (env('APP_ENV') == 'production')) {
            if(getUserId()) {
                $filterSells = DB::connection("DemoTradeMysql")->table(DB::connection("DemoTradeMysql")->raw('(select base_coin_id,trade_coin_id, TRUNCATE(price,8) as price, visualNumberFormat(TRUNCATE(sum(amount-processed),8)) as amount, visualNumberFormat(TRUNCATE(sum((amount - processed) * price), 8)) as total, status, processed, created_at
                                        from sells
                                        where base_coin_id = ' . $base_coin_id . ' and trade_coin_id = ' . $trade_coin_id . ' and  status = 0 and is_market = 0 and deleted_at IS NULL
                                        group by base_coin_id,trade_coin_id,price)
                                     t1'))
                    ->leftJoin('favourite_order_books', ['favourite_order_books.price' => 't1.price',
                        'favourite_order_books.base_coin_id' => 't1.base_coin_id',
                        'favourite_order_books.trade_coin_id' => 't1.trade_coin_id',
                        'favourite_order_books.user_id' => DB::connection("DemoTradeMysql")->raw(getUserId()),
                        'favourite_order_books.type' => DB::connection("DemoTradeMysql")->raw("'sell'")])
                    ->leftJoin(DB::connection("DemoTradeMysql")->raw('(select visualNumberFormat(TRUNCATE(sum(amount-processed),8)) as amount ,price from sells where user_id =' . getUserId() . ' and base_coin_id = ' . $base_coin_id . ' and trade_coin_id = ' . $trade_coin_id . ' and  status = 0 and is_market = 0 and deleted_at IS NULL group by price) t2'), ['t1.price' => 't2.price'])
                    ->select('t1.created_at','t1.status', 't1.processed','t1.price', 't1.amount', 't1.total', 't2.amount as my_size', 'favourite_order_books.id as is_favorite')
                    ->orderBy('t1.price', 'ASC');
            }else{
                $filterSells = DB::connection("DemoTradeMysql")->table(DB::connection("DemoTradeMysql")->raw('(select base_coin_id,trade_coin_id, TRUNCATE(price,8) as price, visualNumberFormat(TRUNCATE(sum(amount-processed),8)) as amount, visualNumberFormat(TRUNCATE(sum((amount - processed) * price), 8)) as total, status, processed, created_at
                                        from sells
                                        where base_coin_id = ' . $base_coin_id . ' and trade_coin_id = ' . $trade_coin_id . ' and  status = 0 and is_market = 0 and deleted_at IS NULL
                                        group by base_coin_id,trade_coin_id,price)
                                     t1'))
                    ->leftJoin('favourite_order_books', ['favourite_order_books.price' => 't1.price',
                        'favourite_order_books.base_coin_id' => 't1.base_coin_id',
                        'favourite_order_books.trade_coin_id' => 't1.trade_coin_id',
                        'favourite_order_books.user_id' => DB::connection("DemoTradeMysql")->raw(0),
                        'favourite_order_books.type' => DB::connection("DemoTradeMysql")->raw("'sell'")])
                    ->select('t1.created_at','t1.status', 't1.processed','t1.price', 't1.amount', 't1.total', DB::connection("DemoTradeMysql")->raw('0 as my_size'), 'favourite_order_books.id as is_favorite')
                    ->orderBy('t1.price', 'ASC');
            }
        }

        return $filterSells;
    }

    public function getTotalAmount($base_coin_id, $trade_coin_id)
    {
        if (true || (env('APP_ENV') == 'local') || (env('APP_ENV') == 'dev')) {
            return DB::connection("DemoTradeMysql")->table('sells')
                ->where(['base_coin_id' => $base_coin_id, 'trade_coin_id' => $trade_coin_id, 'status' => '0', 'is_market' => 0, 'deleted_at' => null])
                ->select('status', DB::connection("DemoTradeMysql")->raw('TRUNCATE(SUM(amount - processed),8) as total'))->groupBy('status')
                ->get();
        }
    }

    public function getPrice($base_coin_id, $trade_coin_id)
    {
        return Sell::where(['base_coin_id' => $base_coin_id, 'trade_coin_id' => $trade_coin_id, 'is_market' => 0, 'status' => 0])->min('price');
    }

    public function getSellMarketPrice($baseCoinId, $tradeCoinId, $amount){//lowest sell price
        try {
            $i = null;
            DB::connection("DemoTradeMysql")->statement(DB::connection("DemoTradeMysql")->raw('set @total=0'));
            $objects = DB::connection("DemoTradeMysql")->select('SELECT id, price, amount, processed, total_offers from (SELECT id, price, amount, processed, @total := @total + Truncate(amount-processed, 8) AS total_offers FROM sells where base_coin_id = ' . $baseCoinId . ' and trade_coin_id = ' . $tradeCoinId . ' and is_market = 0 and status= 0 ORDER BY price asc) tmp
          where tmp.total_offers < ' . $amount . ' or (tmp.total_offers >= ' . $amount . ' AND (tmp.total_offers - Truncate(amount-processed, 8)) < ' . $amount .')');

            $price = 0.0;
            foreach ($objects as $i => $obj) {
                $price = bcadd($obj->price, $price);
            }

            return bcdiv($price, $i + 1);
        }catch (\Exception $e){
            return 0;
        }
    }

    public function getOnOrderBalance($baseCoinId, $tradeCoinId,$userId)
    {
        return  DB::connection("DemoTradeMysql")->table('sells')
            ->where(['user_id' => $userId,'base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId, 'status' => '0', 'is_market' => 0, 'deleted_at' => null])
            ->select( DB::connection("DemoTradeMysql")->raw('TRUNCATE(SUM((amount - processed)),8) as total'))
            ->get();
    }

    public function getMyOrders($baseCoinId, $tradeCoinId, $userId)
    {
//        if(getUserId()) {
        if($userId) {
            return Sell::where(['user_id' => $userId, 'base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId, 'status' => 0, 'is_market' => 0])
                ->select(DB::connection("DemoTradeMysql")->raw("'sell' as type"), 'id', DB::connection("DemoTradeMysql")->raw("visualNumberFormat(price) as price"), 'created_at',
                    DB::connection("DemoTradeMysql")->raw("visualNumberFormat(amount) as actual_amount"), 'processed', 'status', DB::connection("DemoTradeMysql")->raw("visualNumberFormat(TRUNCATE((amount) * price, 8)) as actual_total"),
                    DB::connection("DemoTradeMysql")->raw('visualNumberFormat(TRUNCATE((amount - processed), 8)) as amount, visualNumberFormat(TRUNCATE((amount - processed) * price, 8)) as total,
                    (case when (maker_fees > taker_fees)
                     THEN
                          visualNumberFormat(TRUNCATE((((amount - processed) * price) * maker_fees) * 0.01, 8))
                     ELSE
                          visualNumberFormat(TRUNCATE((((amount - processed) * price) * taker_fees) * 0.01, 8))
                     END)
                     as fees'));
        }else{
            return Sell::where(['user_id' => 0, 'base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId, 'status' => 0, 'is_market' => 0])
                ->select(DB::connection("DemoTradeMysql")->raw("'sell' as type"), 'id', DB::connection("DemoTradeMysql")->raw("visualNumberFormat(price) as price"), 'created_at', DB::connection("DemoTradeMysql")->raw('visualNumberFormat(TRUNCATE((amount - processed), 8)) as amount, visualNumberFormat(TRUNCATE((amount - processed) * price, 8)) as total,
                    (case when (maker_fees > taker_fees)
                     THEN
                          visualNumberFormat(TRUNCATE((((amount - processed) * price) * maker_fees) * 0.01, 8))
                     ELSE
                          visualNumberFormat(TRUNCATE((((amount - processed) * price) * taker_fees) * 0.01, 8))
                     END)
                     as fees'));
        }
    }

}
