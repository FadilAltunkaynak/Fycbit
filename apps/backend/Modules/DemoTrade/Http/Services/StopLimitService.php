<?php

namespace Modules\DemoTrade\Http\Services;


use App\User;
use Illuminate\Http\Request;
use App\Http\Services\Logger;
use App\Jobs\StopLimitProcessJob;
use Illuminate\Support\Facades\DB;
use Modules\DemoTrade\Entities\Buy;
use App\Http\Services\CommonService;
use Illuminate\Support\Facades\Auth;
use Modules\DemoTrade\Entities\Sell;
use App\Http\Services\MyCommonService;
use Modules\DemoTrade\Entities\Wallet;
use Modules\DemoTrade\Entities\CoinPair;
use Modules\DemoTrade\Entities\StopLimit;
use Modules\DemoTrade\Http\Services\CoinPairRepository;
use Modules\DemoTrade\Http\Repositories\BuyOrderRepository;
use Modules\DemoTrade\Http\Repositories\SellOrderRepository;
use Modules\DemoTrade\Http\Repositories\StopLimitRepository;
use Modules\DemoTrade\Http\Repositories\UserWalletRepository;

class StopLimitService extends CommonService
{
    public $model = StopLimit::class;
    public $repository = StopLimitRepository::class;
    public $logger = null;
    public $myCommonService;

    public function __construct()
    {
        parent::__construct($this->model, $this->repository);
        $this->myCommonService = new MyCommonService();
        $this->logger = app(Logger::class);

    }

    public function getOrders()
    {
        return $this->object->getOrders();
    }


    public function create(Request $request)
    {
        $coinPairsService = new CoinPairService();
        $coinPairs = $coinPairsService->getDocs(['parent_coin_id' => $request->base_coin_id, 'child_coin_id' => $request->trade_coin_id ]);
        if(empty($coinPairs)){
            return [
                'status' => false,
                'message' => 'Invalid order request!',
            ];
        }
        if ($request->order == 'buy') {
            if ($request->stop >= $request->limit) {
                return [
                    'status' => false,
                    'message' => __('Stop value must be less than limit value for buy stop limit')
                ];
            }
        } else {
            if ($request->limit >= $request->stop) {
                return [
                    'status' => false,
                    'message' => __('Stop value must be greater than limit value for sell stop limit')
                ];
            }
        }

        $response = false;
        try {
            DB::connection('DemoTradeMysql')->beginTransaction();
            $user = Auth::guard('api')->check() ? Auth::guard('api')->user() : User::find($request->get('user_id'));

            if (empty($user)) {
                DB::connection('DemoTradeMysql')->rollBack();
                return [
                    'status' => false,
                    'message' => __('Invalid user')
                ];
            }

            $walletRepository = new UserWalletRepository(Wallet::class);
            $fees = calculated_fee_limit($user->id);

            if (strtolower($request->order) == 'sell') {
                $walletDetails = $walletRepository->getUserSingleWalletBalance($user->id, $request->trade_coin_id);
                $inputTotal = custom_number_format($request->amount);
            } else {
                $walletDetails = $walletRepository->getUserSingleWalletBalance($user->id, $request->base_coin_id);
                $feesPercent = $fees['maker_fees'] > $fees['taker_fees'] ? $fees['maker_fees'] : $fees['taker_fees'];
                $amountTotal = bcmul($request->limit, $request->amount);
                $inputTotal = bcadd($amountTotal, bcdiv(bcmul($amountTotal, $feesPercent), "100"));
            }

            if (empty($walletDetails)) {
                DB::connection('DemoTradeMysql')->rollBack();
                return [
                    'status' => false,
                    'message' => 'Invalid wallet',
                ];
            }
            $walletBalance = $walletDetails->balance;

            if (bccomp($walletBalance, $inputTotal) < 0) {
                DB::connection('DemoTradeMysql')->rollBack();
                return [
                    'status' => false,
                    'message' => __('You don\'t have enough balance to place a stop limit order.')
                ];
            }

            $stopLimit = [
                'user_id' => $user->id,
                'condition_buy_id' => $request->get('buy_id', null),
                'trade_coin_id' => $request->trade_coin_id,
                'base_coin_id' => $request->base_coin_id,
                'stop' => custom_number_format($request->stop),
                'limit_price' => custom_number_format($request->limit),
                'amount' => custom_number_format($request->amount),
                'order' => $request->order,
                'is_conditioned' => $request->get('is_conditioned', 0),
                'maker_fees' => $fees['maker_fees'],
                'taker_fees' => $fees['taker_fees']
            ];

            $response = $walletRepository->deductBalanceById($walletDetails, $inputTotal);

            if ($response == false) {
                DB::connection('DemoTradeMysql')->rollBack();
                return [
                    'status' => false,
                    'message' => __('Failed to place stop limit. Please try again!')
                ];
            }

            $inserted = $this->object->create($stopLimit);
            if ($inserted) {
                DB::connection('DemoTradeMysql')->commit();
                $this->logger->log('STOP_LIMIT', 'Stop Limit has been placed id: ' . $inserted->id);

                broadcastWalletData($walletDetails->wallet_id);
                //check stop limit already processed
                $repo = new CoinPairRepository(CoinPair::class);
                $coins = $repo->getDocs(['parent_coin_id' => $inserted->base_coin_id, 'child_coin_id' => $inserted->trade_coin_id])->first();
//                dispatch(new StopLimitProcessJob($coins))->onQueue('stop-limit');
                $this->process($coins);
                $request->merge([
                    'dashboard_type'=>'dashboard',
                    'order_type'=>strtolower($request->order)
                ]);
                $d_service = new DashboardService();
                $this->logger->log("REQUEST CHECK", json_encode($request->all()));
                $socket_data = $d_service->getAllOrderSocketData($request);
                $channel_name = 'demo_dashboard-'.$request->base_coin_id.'-'.$request->trade_coin_id;
                $event_name = 'order_place';
                sendDataThroughWebSocket($channel_name,$event_name,$socket_data);
                $socket_data=[];
                if($request->order=='buy'){
                    $X='Buy Order';
                    $x='buy order';
                    $index = 'buy_history';
                }else{
                    $X= 'Sell Order';
                    $x= 'sell order';
                    $index = 'sell_history';
                }
                $socket_data[$index] = $d_service->getMyOrders($request)['data'];
                $request->merge(['order_type' => 'buy_sell']);
                $socket_data['open_orders'] = $d_service->getMyOrders($request)['data'];
                $socket_data['order_data'] = $d_service->getOrderData($request)['data'];
                $channel_name = 'demo_order_place_'.Auth::id();
                sendDataThroughWebSocket($channel_name,$event_name,$socket_data);

                $this->myCommonService->sendNotificationToUserUsingSocket($user->id,"Stop Limit $X","Your stop limit $x placed successfully!");



                return [
                    'status' => true,
                    'message' => __('Stop limit has been placed successfully.'),
                    'data' => $inserted
                ];
            }
            DB::connection('DemoTradeMysql')->rollBack();

            return [
                'status' => false,
                'message' => __('Failed to place stop limit. Please try again!')
            ];
        } catch (\Exception $e) {
            DB::connection('DemoTradeMysql')->rollBack();
            $this->logger->log('STOP_LIMIT_ERROR', 'Error: ' . $e->getMessage() . ' '. $e->getLine());

            return [
                'status' => false,
                'message' => __('Failed to place stop limit. Please try again!')
            ];
        }
    }

//    public function getOnOrderBalanceBuy($baseCoinId, $tradeCoinId, $userId = null)
//    {
//        if ($userId == null) {
//            $userId = Auth::id();
//        }
//
//        return $this->object->getOnOrderBalance($baseCoinId, $tradeCoinId, $userId, 'buy');
//    }
//
//    public function getOnOrderBalanceSell($baseCoinId, $tradeCoinId, $userId = null)
//    {
//        if ($userId == null) {
//            $userId = Auth::id();
//        }
//
//        return $this->object->getOnOrderBalance($baseCoinId, $tradeCoinId, $userId, 'sell');
//    }

    /**
     * Place order of a stop limit
     * @param $coinPair
     * @return bool
     */
    public function process($coinPair)
    {
        $this->logger->log('STOP_LIMIT', 'Coin Pair: ' . $coinPair->parent_coin_id . '_' . $coinPair->child_coin_id);
        try {
            DB::connection('DemoTradeMysql')->beginTransaction();
            $stopLimits = $this->object->getDocs(['status' => 0, 'base_coin_id' => $coinPair->parent_coin_id, 'trade_coin_id' => $coinPair->child_coin_id]);
            foreach ($stopLimits as $stopLimit) {
                $this->logger->log('STOP_LIMIT', 'Start Processing STOP LIMIT ID: ' . $stopLimit->id);
                $this->logger->log('STOP_LIMIT', 'CoinPrice: ' . $coinPair->price . ' Stop Price: ' . $stopLimit->stop);
                $input = [
                    'user_id' => $stopLimit->user_id,
                    'base_coin_id' => $stopLimit->base_coin_id,
                    'trade_coin_id' => $stopLimit->trade_coin_id,
                    'amount' => custom_number_format($stopLimit->amount),
                    'virtual_amount' => bcmul($stopLimit->amount, bcdiv(random_int(20, 80), 100)),
                    'price' => custom_number_format($stopLimit->limit_price),
                    'category' => $stopLimit->category,
                    'is_conditioned' => $stopLimit->is_conditioned,
                    'is_market' => 0,
                    'maker_fees' => $stopLimit->maker_fees,
                    'taker_fees' => $stopLimit->taker_fees
                ];

                if ($stopLimit->condition_buy_id != null) {
                    $input['condition_buy_id'] = $stopLimit->condition_buy_id;
                }

                if (strtolower($stopLimit->order) == 'buy') {
                    //When current price will equal or greater than the stop limit price then a buy order placed.
                    if (bccomp($coinPair->price, $stopLimit->stop) < 0) {
                        continue;
                    }

                    $input['btc_rate'] = getBtcRate($stopLimit->trade_coin_id);
                    $buyOrderRepo = new BuyOrderRepository(Buy::class);
                    $inserted = $buyOrderRepo->create($input);
                    broadcastOrderData($inserted, 'buy', 'orderPlace',$inserted->user_id);
                    $this->logger->log('STOP_LIMIT', 'STOP LIMIT Type: Buy');

                } else {
                    //When current price will equal or less than the stop limit price then a sell order placed.
                    if (bccomp($coinPair->price, $stopLimit->stop) > 0) {
                        continue;
                    }

                    $sellOrderRepo = new SellOrderRepository(Sell::class);
                    if ($stopLimit->is_conditioned == 1) {
                        $advanceSellRemaining = 0;
                        $advanceSells = $sellOrderRepo->getDocs(['condition_buy_id' => $stopLimit->condition_buy_id, 'status' => 0]);

                        if ($advanceSells->isEmpty()) {
                            $stopLimit->update(['status' => 1]);
                            continue;
                        } else {
                            foreach ($advanceSells as $advanceSell) {
                                $advanceSellRemaining = bcadd($advanceSellRemaining, bcsub($advanceSell->amount, $advanceSell->processed));
                                if (bccomp($advanceSell->processed, '0') > 0) {
                                    $advanceSell->update(['amount' => $advanceSell->processed, 'status' => 1]);
                                } else {
                                    $advanceSell->forceDelete();
                                }
                            }
                            if ($advanceSellRemaining > 0) {
                                $stopLimit->update(['amount' => $advanceSellRemaining]);
                                $input['amount'] = $advanceSellRemaining;
                            } else {
                                $stopLimit->update(['status' => 1]);
                                continue;
                            }
                        }
                    }
                    $input['btc_rate'] = getBtcRate($stopLimit->trade_coin_id);
                    $inserted = $sellOrderRepo->create($input);
                    broadcastOrderData($inserted, 'sell', 'orderPlace',$inserted->user_id);
                    $this->logger->log('STOP_LIMIT', 'STOP LIMIT Type: Sell');
                }
                if ($inserted) {
                    $stopLimit->update(['status' => 1]);
                    $this->logger->log('STOP_LIMIT', 'STOP LIMIT ID: ' . $stopLimit->id . ' is closed');
                } else {
                    DB::connection('DemoTradeMysql')->rollBack();
                }
                $this->logger->log('STOP_LIMIT', 'END Processing STOP LIMIT ID: ' . $stopLimit->id);
            }
            DB::connection('DemoTradeMysql')->commit();


            $request = [];
            $request['base_coin_id'] = $coinPair->parent_coin_id;
            $request['trade_coin_id'] = $coinPair->child_coin_id;
            $request['dashboard_type'] = 'dashboard';
            $request['per_page'] = '';
            $time = time();
            $interval = 5;
            $startTime = $time - 864000;
            $endTime = $time;
            $socket_data = [];
            $d_service = new DashboardService();
            $socket_data['trades'] = $d_service->getMarketTransactions((object) $request)['data'];
            $socket_data['last_trade'] = $d_service->getMarketLastTransactions((object) $request)['data'];
//            $chartService = new TradingViewChartService();
//            $socket_data['chart'] = $chartService->getChartData($startTime, $endTime, $interval, $coinPair->parent_coin_id, $coinPair->child_coin_id,1);
            $channel_name = 'demo_trade-info-'.$coinPair->parent_coin_id.'-'.$coinPair->child_coin_id;
            $event_name = 'process';
            $socket_data['summary'] = $d_service->getOrderData((object) $request)['data'];
            $socket_data['update_trade_history'] = true;
            sendDataThroughWebSocket($channel_name,$event_name,$socket_data);

            return true;
        } catch (\Exception $exception) {
            DB::connection('DemoTradeMysql')->rollBack();
            $this->logger->log('STOP_LIMIT_ERROR', 'Error: ' . $exception->getMessage());

            return false;
        }
    }

    public function getMyStopLimitOrders($request)
    {
        return $this->object->getMyOrders($request);
    }

}
