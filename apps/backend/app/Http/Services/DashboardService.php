<?php


namespace App\Http\Services;

use Exception;
use App\Model\Buy;
use App\Model\Sell;
use App\Model\Wallet;
use App\Model\CoinPair;
use App\Model\SelectedCoinPair;
use Illuminate\Support\Facades\DB;
use App\Traits\ResponseFormatTrait;
use Illuminate\Support\Facades\Auth;
use App\Dtos\Calculate24HourPriceDto;
use App\Http\Repositories\BuyOrderRepository;
use App\Http\Repositories\CoinPairRepository;
use App\Http\Repositories\DashboardRepository;
use App\Http\Repositories\SellOrderRepository;
use App\Http\Repositories\UserWalletRepository;
use App\Services\TradeSettingServices\TradeFeeFinderService;

class DashboardService
{
    use ResponseFormatTrait;

    public $repository;
    private SellOrderRepository $sellOrderRepository;
    private BuyOrderRepository $buyOrderRepository;
    private TradeFeeFinderService $tradeFeeFinderService;

    public function __construct()
    {
        $this->repository = new DashboardRepository();
        $this->sellOrderRepository = new SellOrderRepository(Sell::class);
        $this->buyOrderRepository = new BuyOrderRepository(Buy::class);
        $this->tradeFeeFinderService = app()->make(TradeFeeFinderService::class);
    }

    public function _getTradeCoin()
    {
        $repo = new DashboardRepository();
        $selectedCoinPair = $repo->getDocs(['user_id' => getUserId()])->first();

        if (!empty($selectedCoinPair)) {
            return $selectedCoinPair->trade_coin_id;
        } else {
            return 1;
        }
    }

    public function _getBaseCoin()
    {
        $repo = new DashboardRepository();
        $selectedCoinPair = $repo->getDocs(['user_id' => getUserId()])->first();
        if (!empty($selectedCoinPair)) {
            return $selectedCoinPair->base_coin_id;
        } else {
            return 2;
        }
    }

    public function getCoinPairForUser($baseCoinId, $tradeCoinId, $userId)
    {
        $repo = new CoinPairRepository(CoinPair::class);

        return $repo->getCoinPairDataForUser($baseCoinId, $tradeCoinId, $userId);
    }

    public function getCoinPair($baseCoinId, $tradeCoinId)
    {

        $repo = new CoinPairRepository(CoinPair::class);

        return $repo->getCoinPairsData($baseCoinId, $tradeCoinId);
    }

    public function _setTradeCoin($tradeCoinId)
    {
        $repo = new DashboardRepository();
        $selectedCoinPair = $repo->getDocs(['user_id' => getUserId()])->first();
        if (!empty($selectedCoinPair)) {
            return $repo->updateWhere(['user_id' => getUserId()], ['trade_coin_id' => $tradeCoinId]);
        } else {
            return SelectedCoinPair::create(['user_id' => getUserId(), 'trade_coin_id' => 1, 'base_coin_id' => 2]);
        }
    }

    public function _setBaseCoin($baseCoinId)
    {
        $repo = new DashboardRepository();
        $selectedCoinPair = $repo->getDocs(['user_id' => getUserId()])->first();
        if (!empty($selectedCoinPair)) {
            return $repo->updateWhere(['user_id' => getUserId()], ['base_coin_id' => $baseCoinId]);
        } else {
            return SelectedCoinPair::create(['user_id' => getUserId(), 'trade_coin_id' => 1, 'base_coin_id' => 2]);
        }
    }

    public function getLastPriceList($baseCoinId = null, $tradeCoinId = null)
    {
        if ($baseCoinId == null && $tradeCoinId == null) {
            return \App\Model\CoinPair::orderBy('created_at', 'desc');
        } elseif ($baseCoinId != null && $tradeCoinId == null) {
            return CoinPair::where(['parent_coin_id' => $baseCoinId])->orderBy('created_at', 'desc');
        } elseif ($baseCoinId == null && $tradeCoinId != null) {
            return CoinPair::where(['child_coin_id' => $tradeCoinId])->orderBy('created_at', 'desc');
        } elseif ($baseCoinId != null && $tradeCoinId != null) {
            return CoinPair::where(['parent_coin_id' => $baseCoinId, 'child_coin_id' => $tradeCoinId])->orderBy('created_at', 'desc');
        }
    }

    public function getOnOrderBalance($baseCoinId, $tradeCoinId, $userId = null)
    {
        $data['total_buy'] = $this->repository->getOnOrderBalance($baseCoinId, $userId);
        $data['total_sell'] = $this->repository->getOnOrderBalance($tradeCoinId, $userId);

        return $data;
    }

    public function getTotalVolume($baseCoinId, $tradeCoinId)
    {
        $cachedVolume = app('spot_orderbook_cache')->getTotalVolume($baseCoinId, $tradeCoinId);
        $data['total_buy_amount'] = truncate_num($cachedVolume['total_buy_amount']);
        $data['buy_price'] = app('spot_orderbook_cache')->getMaxMinPrice($tradeCoinId, $baseCoinId, 'buy');
        $data['total_sell_amount'] = truncate_num($cachedVolume['total_sell_amount']);
        $data['sell_price'] = app('spot_orderbook_cache')->getMaxMinPrice($tradeCoinId, $baseCoinId, 'sell');

        return $data;
    }

    // get order data

    public function getOrderData($request)
    {
        $response = [
            'status' => false,
            'message' => __('Something went wrong'),
            'data' => []
        ];
        $baseCoinId = $request->base_coin_id;
        $tradeCoinId = $request->trade_coin_id;
        try {
            if (Auth::guard('api')->check()) {
                if (empty($baseCoinId) || empty($tradeCoinId)) {

                    $tradeCoinId = $this->_getTradeCoin();
                    $baseCoinId = $this->_getBaseCoin();

                    $data['base_coin_id'] = $baseCoinId;
                    $data['trade_coin_id'] = $tradeCoinId;
                } else {
                    $data['base_coin_id'] = $baseCoinId;
                    $data['trade_coin_id'] = $tradeCoinId;
                }
                $baseCoinData = $this->getCoinPair($baseCoinId, $tradeCoinId);
                $price24hData = TransactionService::calculate24HourData(Calculate24HourPriceDto::fromCoinPair($baseCoinData));

                $data['base_coin_id'] = $baseCoinData->parent_coin_id;
                $data['trade_coin_id'] = $baseCoinData->child_coin_id;
                $data['total']['trade_wallet']['wallet_id'] = $baseCoinData->wallet_id;
                $data['total']['trade_wallet']['balance'] = $baseCoinData->balance;
                $data['total']['trade_wallet']['coin_type'] = $baseCoinData->child_coin_name;
                $data['total']['trade_wallet']['full_name'] = $baseCoinData->child_full_name;
                $data['total']['trade_wallet']['high'] = $price24hData['high'];
                $data['total']['trade_wallet']['low'] = $price24hData['low'];
                $data['total']['trade_wallet']['pair_decimal'] = $baseCoinData->pair_decimal;
                $data['total']['trade_wallet']['volume'] = $price24hData['volume'];
                $data['total']['trade_wallet']['last_price'] = $baseCoinData->last_price;
                $data['total']['trade_wallet']['price_change'] = $price24hData['change'];

                $walletService = new UserWalletService();
                $wallet = $walletService->getBalance(getUserId(), $baseCoinData->parent_coin_id);

                $data['total']['base_wallet']['balance'] = json_decode($wallet)->balance;
                $data['total']['base_wallet']['wallet_id'] = json_decode($wallet)->wallet_id;
                $data['total']['base_wallet']['coin_type'] = $baseCoinData->parent_coin_name;
                $data['total']['base_wallet']['full_name'] = $baseCoinData->parent_full_name;
                $data['total']['base_wallet']['volume'] = convert_currency($price24hData['volume'], $baseCoinData->parent_coin_name, $baseCoinData->child_coin_name);
                $tradeFees = $this->tradeFeeFinderService->findTradeFee($baseCoinId, $tradeCoinId, getUserId());

                $data['fees'] = [
                    'maker_fees' => $tradeFees->maker_fee,
                    'taker_fees' => $tradeFees->taker_fee
                ];

                $onOrder = $this->getOnOrderBalance($baseCoinId, $tradeCoinId);
                $data['on_order']['trade_wallet'] = $onOrder['total_sell'];
                $data['on_order']['base_wallet'] = $onOrder['total_buy'];

                $data['on_order']['trade_wallet_total'] = bcaddx($onOrder['total_sell'], $baseCoinData->balance, 8);
                $data['on_order']['base_wallet_total'] = bcaddx($onOrder['total_buy'], $data['total']['base_wallet']['balance'], 8);

                $price = $this->getTotalVolume($baseCoinId, $tradeCoinId);
                $data['sell_price'] = $price['sell_price'] > 0 ? $price['sell_price'] : $baseCoinData->last_price;
                $data['buy_price'] = $price['buy_price'] > 0 ? $price['buy_price'] : $baseCoinData->last_price;
            } else {
                if (empty($tradeCoinId) || empty($baseCoinId)) {
                    $tradeCoinId = 1;
                    $baseCoinId = 2;
                }
                $repo = new CoinPairRepository(CoinPair::class);
                $baseCoinData = $repo->getCoinPairsData($baseCoinId, $tradeCoinId);
                $price24hData = TransactionService::calculate24HourData(Calculate24HourPriceDto::fromCoinPair($baseCoinData));

                $data['is_token'] = $baseCoinData->is_token;
                $data['bot_trading'] = $baseCoinData->bot_trading;
                $data['base_coin_id'] = $baseCoinData->parent_coin_id;
                $data['trade_coin_id'] = $baseCoinData->child_coin_id;
                $data['total']['trade_wallet']['wallet_id'] = "";
                $data['total']['trade_wallet']['balance'] = $baseCoinData->balance;
                $data['total']['trade_wallet']['coin_type'] = $baseCoinData->child_coin_name;
                $data['total']['trade_wallet']['full_name'] = $baseCoinData->child_full_name;
                $data['total']['trade_wallet']['high'] = $price24hData['high'];
                $data['total']['trade_wallet']['low'] = $price24hData['low'];
                $data['total']['trade_wallet']['pair_decimal'] = $baseCoinData->pair_decimal;
                $data['total']['trade_wallet']['volume'] = $price24hData['volume'];
                $data['total']['trade_wallet']['last_price'] = $baseCoinData->last_price;
                $data['total']['trade_wallet']['price_change'] = $price24hData['change'];

                $data['total']['base_wallet']['balance'] = 0;
                $data['total']['base_wallet']['wallet_id'] = "";
                $data['total']['base_wallet']['coin_type'] = $baseCoinData->parent_coin_name;
                $data['total']['base_wallet']['full_name'] = $baseCoinData->parent_full_name;
                $data['total']['base_wallet']['volume'] = convert_currency($price24hData['volume'], $baseCoinData->parent_coin_name, $baseCoinData->child_coin_name);

                $data['fees'] = 0;
                $data['on_order']['trade_wallet'] = 0;
                $data['on_order']['base_wallet'] = 0;
                $data['on_order']['trade_wallet_total'] = 0;
                $data['on_order']['base_wallet_total'] = 0;

                $price = $this->getTotalVolume($baseCoinId, $tradeCoinId);
                $data['sell_price'] = $price['sell_price'] > 0 ? $price['sell_price'] : $baseCoinData->last_price;
                $data['buy_price'] = $price['buy_price'] > 0 ? $price['buy_price'] : $baseCoinData->last_price;
            }
            $data['base_coin'] = get_coin_type($data['base_coin_id']);
            $data['trade_coin'] = get_coin_type($data['trade_coin_id']);
            $data['exchange_pair'] = $data['trade_coin'] . '_' . $data['base_coin'];
            $data['exchange_coin_pair'] = $data['trade_coin'] . '/' . $data['base_coin'];

            $response = [
                'status' => true,
                'message' => __('Data get successfully'),
                'data' => $data
            ];

            return $response;
        } catch (\Exception $exception) {
            storeException('get order data exception--> ', $exception->getMessage());
            return [
                'status' => false,
                'message' => __('Something went wrong. Please try again!' . getError($exception)),
                'data' => []
            ];
        }
    }

    // get all orders
    public function getOrders($request)
    {
        $response = [
            'status' => false,
            'message' => __('Something went wrong'),
            'data' => []
        ];
        try {
            $setting_per_page = isset(allsetting()['user_pagination_limit']) ? allsetting()['user_pagination_limit'] : 50;
            $perPage = empty($request->per_page) ? $setting_per_page : $request->per_page;

            $volume = $this->getTotalVolume($request->base_coin_id, $request->trade_coin_id);
            if ($request->order_type == 'sell') {
                if (isset($request->dashboard_type) && $request->dashboard_type == 'dashboard') {
                    $data['orders'] = app('spot_orderbook_cache')->getOrdersData($request->base_coin_id, $request->trade_coin_id, 'sell');
                } else {
                    $data['orders'] = app('spot_orderbook_cache')->getOrdersData($request->base_coin_id, $request->trade_coin_id, 'sell');
                }
                $data['order_type'] = 'sell';
                $data['total_volume'] = $volume['total_sell_amount'];
                $response = [
                    'status' => true,
                    'message' => '',
                    'data' => $data
                ];
            } else if ($request->order_type == 'buy') {
                if (isset($request->dashboard_type) && $request->dashboard_type == 'dashboard') {
                    $data['orders'] = app('spot_orderbook_cache')->getOrdersData($request->base_coin_id, $request->trade_coin_id, 'buy');
                } else {
                    $data['orders'] = app('spot_orderbook_cache')->getOrdersData($request->base_coin_id, $request->trade_coin_id, 'buy');
                }
                $data['order_type'] = 'buy';
                $data['total_volume'] = $volume['total_buy_amount'];
                $response = [
                    'status' => true,
                    'message' => '',
                    'data' => $data
                ];
            } else {
                if (isset($request->dashboard_type) && $request->dashboard_type == 'dashboard') {
                    $data['buy_orders'] = app('spot_orderbook_cache')->getOrdersData($request->base_coin_id, $request->trade_coin_id, 'buy');
                    $data['sell_orders'] = app('spot_orderbook_cache')->getOrdersData($request->base_coin_id, $request->trade_coin_id, 'sell');
                } else {
                    $data['buy_orders'] = app('spot_orderbook_cache')->getOrdersData($request->base_coin_id, $request->trade_coin_id, 'buy');
                    $data['sell_orders'] = app('spot_orderbook_cache')->getOrdersData($request->base_coin_id, $request->trade_coin_id, 'sell');
                }
                $data['order_type'] = 'buy_sell';
                $data['total_sell_volume'] = $volume['total_sell_amount'];
                $data['total_buy_volume'] = $volume['total_buy_amount'];
                $response = [
                    'status' => true,
                    'message' => '',
                    'data' => $data
                ];
            }
        } catch (\Exception $e) {
            storeException('get all order exception -> ', $e->getMessage());
        }

        return $response;
    }

    // get my orders

    public function getMyOrders($request)
    {
        $response = [
            'status' => false,
            'message' => __('Something went wrong'),
            'data' => []
        ];
        try {
            $userId = $request->userId ?? getUserId();
            $setting_per_page = isset(allsetting()['user_pagination_limit']) ? allsetting()['user_pagination_limit'] : 10;
            $perPage = empty($request->per_page) ? $setting_per_page : $request->per_page;

            if ($request->order_type == 'sell') {
                $sellOrderService = new SellOrderService();
                if (isset($request->dashboard_type) && $request->dashboard_type == 'dashboard') {
                    $data['orders'] = $sellOrderService->getMyOrders($request->base_coin_id, $request->trade_coin_id, $userId)->limit(20)->get();
                } else {
                    $data['orders'] = $sellOrderService->getMyOrders($request->base_coin_id, $request->trade_coin_id, $userId)->paginate($perPage)->appends($request->all());
                }
                $response = [
                    'status' => true,
                    'message' => '',
                    'data' => $data
                ];
            } else if ($request->order_type == 'buy') {
                $buyOrderService = new BuyOrderService();
                if (isset($request->dashboard_type) && $request->dashboard_type == 'dashboard') {
                    $data['orders'] = $buyOrderService->getMyOrders($request->base_coin_id, $request->trade_coin_id, $userId)->limit(20)->get();
                } else {
                    $data['orders'] = $buyOrderService->getMyOrders($request->base_coin_id, $request->trade_coin_id, $userId)->paginate($perPage)->appends($request->all());
                }
                $response = [
                    'status' => true,
                    'message' => '',
                    'data' => $data
                ];
            } else {
                $sellOrderService = new SellOrderService();
                $sellOrders = $sellOrderService->getMyOrders($request->base_coin_id, $request->trade_coin_id, $userId)->get()->toArray();
                $buyOrderService = new BuyOrderService();
                $buyOrders = $buyOrderService->getMyOrders($request->base_coin_id, $request->trade_coin_id, $userId)->get()->toArray();
                $data['orders'] = array_merge($buyOrders, $sellOrders);
                $data['buy_orders'] = $buyOrders;
                $data['sell_orders'] = $sellOrders;
                usort($data['orders'], function ($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                });

                $response = [
                    'status' => true,
                    'message' => '',
                    'data' => $data
                ];
            }
        } catch (\Exception $e) {
            storeException('get my order exception -> ', $e->getMessage());
        }

        return $response;
    }


    // get my transaction
    public function getMyTradeHistory($request)
    {
        $response = [
            'status' => false,
            'message' => __('Something went wrong'),
            'data' => []
        ];
        try {
            $userId = isset($request->user_id) ? $request->user_id : getUserId();
            $setting_per_page = isset(allsetting()['user_pagination_limit']) ? allsetting()['user_pagination_limit'] : 10;
            $perPage = empty($request->per_page) ? $setting_per_page : $request->per_page;

            $transactionService = new TransactionService();
            if ($request->per_page == 'all') {
                $data['transactions'] = $transactionService->getMyTradeHistory($request->base_coin_id, $request->trade_coin_id, $userId, $request->order_type ?? null, 0)->get();
            } else {
                if (isset($request->dashboard_type) && $request->dashboard_type == 'dashboard') {
                    $data['transactions'] = $transactionService->getMyTradeHistory($request->base_coin_id, $request->trade_coin_id, $userId, $request->order_type ?? null, $request->duration ?? null)->limit(20)->get();
                } else {
                    $data['transactions'] = $transactionService->getMyTradeHistory($request->base_coin_id, $request->trade_coin_id, $userId, $request->order_type ?? null, $request->duration ?? null)->paginate($perPage)->appends($request->all());
                }
            }
            $response = [
                'status' => true,
                'message' => __('Data get successfully'),
                'data' => $data
            ];
        } catch (\Exception $e) {
            storeException('get my trade history exception -> ', $e->getMessage());
        }

        return $response;
    }

    public function getMarketTransactions($request)
    {
        try {
            $user_pagination_limit = allsetting('user_pagination_limit');
            $setting_per_page = $user_pagination_limit ? $user_pagination_limit : 10;
            $perPage = empty($request->per_page) ? $setting_per_page : $request->per_page;

            $checkPair = checkPair($request->base_coin_id, $request->trade_coin_id);
            if ($checkPair == false)
                throw new Exception(__("Pair not found"));

            $transactionService = new TransactionService();
            $query = $transactionService->getAllTradeHistory($request->base_coin_id, $request->trade_coin_id);

            $data['transactions'] = (@$request->dashboard_type == 'dashboard')
                ? $query->limit($perPage)->get()
                : $query->paginate($perPage)->appends($request->all());

            return $this->responseData(true, __('Data get successfully'), $data);
        } catch (\Exception $e) {
            storeLog(processExceptionMsg($e), "error");
            return $this->responseData(false);
        }
    }

    // get two market trade data
    public function getDashboardMarketTradeDataTwo($base_coin_id, $trade_coin_id, $limit)
    {
        $request = (object) [
            'base_coin_id' => $base_coin_id,
            'trade_coin_id' => $trade_coin_id,
            'limit' => $limit
        ];
        $data = app('spot_transaction_cache')->getLastPrice($request);
        if(empty($data)) {
            $data = app('spot_coinpair_cache')->getLastPrice($request);
        }
        return $data;
    }

    public function getMarketLastTransactions($request)
    {
        $response = [
            'status' => false,
            'message' => __('Something went wrong'),
            'data' => []
        ];
        try {
            $transactionService = new TransactionService();
            $transaction = $transactionService->getLastTradeHistory($request->base_coin_id, $request->trade_coin_id);
            $response = [
                'status' => true,
                'message' => __('Data get successfully'),
                'data' => $transaction
            ];
        } catch (\Exception $e) {
            storeException('get getMarketLastTransactions history exception -> ', $e->getMessage());
        }

        return $response;
    }

    // order all socket data
    public function getAllOrderSocketData($request)
    {
        //        $pairservice = new CoinPairService();
        //        $data['pairs'] = $pairservice->getAllCoinPairs()['data'];
        $data['order_data'] = $this->getOrderData($request)['data'];
        //        $data['last_price_data'] = $this->getDashboardMarketTradeDataTwo($request->base_coin_id, $request->trade_coin_id,2);
        $data['orders'] = $this->getOrders($request)['data'];
        if (isset($data['orders']['orders'][0])) {
            foreach ($data['orders']['orders'] as $order) {
                if (isset($request->price)) {
                    if ($request->price == $order->price) {
                        $order->percentage = bcdivx(bcmulx($order->amount, 100, 8), $request->amount, 2);
                    } else {
                        $order->percentage = rand(0, 50);
                    }
                } else {
                    $order->percentage = rand(0, 50);
                }
            }
        }
        return $data;
    }

    public function getOrderDataTotal($request)
    {
        $response = [
            'status' => false,
            'message' => __('Something went wrong'),
            'data' => []
        ];
        $baseCoinId = $request->base_coin_id;
        $tradeCoinId = $request->trade_coin_id;
        try {
            $data['base_coin_id'] = $baseCoinId;
            $data['trade_coin_id'] = $tradeCoinId;

            $baseCoinData = $this->getCoinPairWithUserId($baseCoinId, $tradeCoinId, $request->userId);
            $price24hData = TransactionService::calculate24HourData(Calculate24HourPriceDto::fromCoinPair($baseCoinData));

            $data['base_coin_id'] = $baseCoinData->parent_coin_id;
            $data['trade_coin_id'] = $baseCoinData->child_coin_id;
            $data['total']['trade_wallet']['balance'] = $baseCoinData->balance;
            $data['total']['trade_wallet']['coin_type'] = $baseCoinData->child_coin_name;
            $data['total']['trade_wallet']['full_name'] = $baseCoinData->child_full_name;
            $data['total']['trade_wallet']['high'] = $price24hData['high'];
            $data['total']['trade_wallet']['low'] = $price24hData['low'];
            $data['total']['trade_wallet']['volume'] = $price24hData['volume'];
            $data['total']['trade_wallet']['last_price'] = $baseCoinData->last_price;
            $data['total']['trade_wallet']['price_change'] = $price24hData['change'];
            $data['total']['trade_wallet']['pair_decimal'] = $baseCoinData->pair_decimal;

            $walletService = new UserWalletService();
            $wallet = $walletService->getBalance($request->userId, $baseCoinData->parent_coin_id);
            if ($request->userId == 1) {
                $data['total']['base_wallet']['balance'] = 0;
            } else {
                $data['total']['base_wallet']['balance'] = json_decode($wallet)->balance;
            }

            $data['total']['base_wallet']['coin_type'] = $baseCoinData->parent_coin_name;
            $data['total']['base_wallet']['full_name'] = $baseCoinData->parent_full_name;

            $tradeFees = $this->tradeFeeFinderService->findTradeFee(
                $baseCoinId,
                $tradeCoinId,
                $request->userId
            );

            $data['fees'] = [
                'maker_fees' => $tradeFees->maker_fee,
                'taker_fee' => $tradeFees->taker_fee,
            ];

            $response = [
                'status' => true,
                'message' => __('Data get successfully'),
                'data' => $data
            ];

            return $response;
        } catch (\Exception $exception) {
            storeException('get order data total exception--> ', $exception->getMessage());
            return [
                'status' => false,
                'message' => __('Something went wrong. Please try again!' . getError($exception)),
                'data' => []
            ];
        }
    }

    public function getCoinPairWithUserId($baseCoinId, $tradeCoinId, $userId)
    {
        if (empty($tradeCoinId) || empty($baseCoinId)) {
            $tradeCoinId = $this->_getTradeCoin();
            $baseCoinId = $this->_getBaseCoin();
        } else {
            $this->_setTradeCoin($tradeCoinId);
            $this->_setBaseCoin($baseCoinId);
        }

        $repo = new CoinPairRepository(CoinPair::class);

        return $repo->getCoinPairsDataWithUser($baseCoinId, $tradeCoinId, $userId);
    }

    // get order data for order process

    public function getOrderDataWhenProcess($request)
    {
        $response = [
            'status' => false,
            'message' => __('Something went wrong'),
            'data' => []
        ];
        $baseCoinId = $request->base_coin_id;
        $tradeCoinId = $request->trade_coin_id;
        try {
            $baseCoinData = app('spot_coinpair_cache')->getCoinPair($tradeCoinId, $baseCoinId);

		if(!isset($baseCoinData['parent_coin_id'])){
			logger($request);
			logger('7777');
			logger($baseCoinData);
		}

            $data['base_coin_id'] = (int) $baseCoinData['parent_coin_id'];
            $data['trade_coin_id'] = (int) $baseCoinData['child_coin_id'];
            $data['total']['trade_wallet']['coin_type'] = $baseCoinData['child_coin_name'];
            $data['total']['trade_wallet']['full_name'] = $baseCoinData['child_full_name'];
            $data['total']['trade_wallet']['high'] = $baseCoinData['high'];
            $data['total']['trade_wallet']['low'] = $baseCoinData['low'];
            $data['total']['trade_wallet']['volume'] = $baseCoinData['volume'];
            $data['total']['trade_wallet']['last_price'] = $baseCoinData['last_price'];
            $data['total']['trade_wallet']['price_change'] = $baseCoinData['price_change'];
            $data['total']['trade_wallet']['pair_decimal'] = (int) $baseCoinData['pair_decimal'];

            $data['total']['base_wallet']['balance'] = 0;
            $data['total']['base_wallet']['coin_type'] = $baseCoinData['parent_coin_name'];
            $data['total']['base_wallet']['full_name'] = $baseCoinData['parent_full_name'];
            $data['total']['base_wallet']['volume'] = bcmulx($baseCoinData['volume'], $baseCoinData['last_price'], 18);

            $data['fees'] = 0;
            $data['on_order']['trade_wallet'] = 0;
            $data['on_order']['base_wallet'] = 0;

            $data['base_coin'] = $baseCoinData['parent_coin_name'];
            $data['trade_coin'] = $baseCoinData['child_coin_name'];
            $data['exchange_pair'] = $baseCoinData['child_coin_name'] . '_' . $baseCoinData['parent_coin_name'];
            $data['exchange_coin_pair'] = $baseCoinData['child_coin_name'] . '/' . $baseCoinData['parent_coin_name'];

            $response = [
                'status' => true,
                'message' => __('Data get successfully'),
                'data' => $data
            ];

            return $response;
        } catch (\Exception $exception) {
            storeException('get order data exception--> ', $exception->getMessage());
            return [
                'status' => false,
                'message' => __('Something went wrong. Please try again!' . getError($exception)),
                'data' => []
            ];
        }
    }

    /**
     * Get Total Earning from users withdrawals to usdt
     * @return float
     */
    public function getWithdrawalTotalEarning(): float
    {
        return DB::table('withdraw_histories as w')
            ->leftJoin('coins as c', 'w.coin_type', '=', 'c.coin_type')
            ->leftJoin('coin_pairs as cp', function ($join) {
                $join->on('cp.child_coin_id', '=', 'c.id')
                    ->where('cp.parent_coin_id', function ($sub) {
                        $sub->select('id')
                            ->from('coins')
                            ->where('coin_type', 'USDT')
                            ->limit(1);
                    });
            })
            ->where('w.status', 1)
            ->selectRaw('SUM(w.fees * COALESCE(cp.price, c.coin_price)) as total_usdt')
            ->value('total_usdt') ?? 0;
    }
}
