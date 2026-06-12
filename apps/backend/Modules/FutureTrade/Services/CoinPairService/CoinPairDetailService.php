<?php

namespace Modules\FutureTrade\Services\CoinPairService;

use App\Model\Coin;
use App\Model\CurrencyList;
use App\Model\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\FutureTrade\DataObject\Calculate24HourPriceDto;
use Modules\FutureTrade\Entities\FutureUserSetting;
use Modules\FutureTrade\Repositories\CoinPairRepository\CoinPairDetailRepository;
use Modules\FutureTrade\Repositories\WalletRepository\FutureWalletRepository;
use Modules\FutureTrade\Services\TradeServices\TradeService;

class CoinPairDetailService
{
    private CoinPairDetailRepository $repository;

    public function __construct()
    {
        $this->repository = new CoinPairDetailRepository();
    }

    /**
     * Get Coin Pair Details with 24-hour Change Data
     * 
     * @param string $code Coin pair code
     * @return array
     */
    public function getCoinPairDetailsByCoinCode(string $code): array
    {
        try {
            if (empty($code)) {
                return [
                    'success' => false,
                    'message' => __('Coin code is required'),
                    'data' => null
                ];
            }

            $coinPairDetails = $this->repository->getCoinPairWithDetails($code);

            if (!$coinPairDetails) {
                return [
                    'success' => false,
                    'message' => __('Coin pair not found'),
                    'data' => null
                ];
            }
            
            if (data_get($coinPairDetails, 'status') != 1) {
                return [
                    'success' => false,
                    'message' => __('Coin pair is not enable for trading'),
                    'data' => null
                ];
            }

            // create default user setting for leverage and margin mode
            $userSetting = FutureUserSetting::where('coin_pair_id', $coinPairDetails['id'])->where('user_id', authId())->exists();
            if(Auth::check() && !$userSetting){
                FutureUserSetting::create([
                    'uid' => Str::uuid()->getHex(),
                    'user_id' => authId(),
                    'coin_pair_id' => data_get($coinPairDetails, 'id'),
                    'leverage' => 1,
                    'margin_mode' => 1,
                ]);
                FutureWalletRepository::getWallet(data_get($coinPairDetails, 'trade_coin_id'), authId());
            }

            $tradeDetails = app(TradeService::class)->repository->getLastTrade(
                coin_id: data_get($coinPairDetails, 'id'),
            );

            $changeData = TradeService::calculate24HourData(
                pairDto: Calculate24HourPriceDto::fromParam(
                    base_id: data_get($coinPairDetails, 'base_coin_id'),
                    trade_id: data_get($coinPairDetails, 'trade_coin_id'),
                )
            );

            $market_price = app('future-cache')->getMarketPrice(data_get($coinPairDetails, 'id')) ?? 0;
            $market_price = trim_num((string) $market_price ?: 0, data_get($coinPairDetails, 'trade_decimal') ?? 8) ?: '0';

            $markPrice = app('future-cache')->getMarkPrice(data_get($coinPairDetails, 'id')) ?? 0;
            $markPrice = trim_num((string) $markPrice, data_get($coinPairDetails, 'trade_decimal') ?? 8) ?: '0';

            $indexPrice = app('future-cache')->getIndexPrice(data_get($coinPairDetails, 'id')) ?? 0;
            $indexPrice = trim_num((string) $indexPrice, data_get($coinPairDetails, 'trade_decimal') ?? 8) ?: '0';

            $previous_price = trim_num((string) $tradeDetails?->last_price ?: 0, data_get($coinPairDetails, 'trade_decimal') ?? 8) ?: 0;
            $previous_price = $previous_price ?: $market_price ?: '0';

            if (bccompx($market_price, '0', 8) == 0) {
                $market_price = convert_currency(1, data_get($coinPairDetails, 'trade_coin_code'), data_get($coinPairDetails, 'base_coin_code'));
                app('future-cache')->setMarketPrice(data_get($coinPairDetails, 'id'), $market_price);
            }

            $responseData = array_merge($coinPairDetails, [
                'previous_price' => $previous_price,
                'market_price' => $market_price,
                'mark_price' => $markPrice,
                'index_price' => $indexPrice,
                'change' => $changeData['change'] ?: '0',
                'high' => $changeData['high'] ?: '0',
                'low' => $changeData['low'] ?: '0',
                'base_volume' => $changeData['volume'] ?: '0',
                'trade_volume' => bcmulx($changeData['volume'] ?? 0, $markPrice ?? '0', $coinPairDetails['trade_decimal']),
            ]);

            return [
                'success' => true,
                'message' => __('Coin pair details retrieved successfully'),
                'data' => $responseData
            ];
        } catch (\Exception $e) {
            errorLogger('FutureTrade CoinPairDetailService Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => __('Error retrieving coin pair details'),
                'data' => null
            ];
        }
    }

    /**
     * Get Multiple Coin Pairs Details with 24-hour Change Data
     * 
     * @param array $codes Array of coin pair codes
     * @return array
     */
    public function getMultipleCoinPairDetails(array $codes): array
    {
        try {
            if (empty($codes)) {
                return [
                    'success' => false,
                    'message' => __('At least one coin code is required'),
                    'data' => []
                ];
            }

            $coinPairs = [];

            foreach ($codes as $code) {
                $result = $this->getCoinPairDetailsByCoinCode($code);

                if ($result['success']) {
                    $coinPairs[] = $result['data'];
                }
            }

            if (empty($coinPairs)) {
                return [
                    'success' => false,
                    'message' => __('No coin pairs found'),
                    'data' => []
                ];
            }

            return [
                'success' => true,
                'message' => __('Coin pairs details retrieved successfully'),
                'data' => $coinPairs
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => __('Error retrieving coin pairs details'),
                'data' => []
            ];
        }
    }

    /**
     * Get All Active Coin Pairs with 24-hour Change Data
     * 
     * @return array
     */
    public function getAllActiveCoinPairsWithChangeData(): array
    {
        try {
            $coinPairs = $this->repository->getAllActiveCoinPairs();

            if ($coinPairs->isEmpty()) {
                return [
                    'success' => false,
                    'message' => __('No active coin pairs found'),
                    'data' => []
                ];
            }

            $formattedCoinPairs = [];

            foreach ($coinPairs as $coinPair) {
                $changeData = TradeService::calculate24HourData(
                    pairDto: Calculate24HourPriceDto::fromParam(
                        base_id: $coinPair->base_coin_id,
                        trade_id: $coinPair->trade_coin_id,
                    )
                );

                $lastPrice = app('future-cache')->getMarketPrice($coinPair->id) ?? 0;

                $formattedCoinPairs[] = [
                    'id' => $coinPair->id,
                    'uid' => $coinPair->uid,
                    'code' => $coinPair->code,
                    'base_coin_code' => $coinPair->base_coin_code,
                    'trade_coin_code' => $coinPair->trade_coin_code,
                    'base_decimal' => $coinPair->base_decimal,
                    'trade_decimal' => $coinPair->trade_decimal,
                    'last_price' => trim_num((string) $lastPrice, $coinPair->trade_decimal) ?: '0',
                    'change' => $changeData['change'],
                    'high' => $changeData['high'],
                    'low' => $changeData['low'],
                    'volume' => $changeData['volume'],
                    'maker_fees_percent' => $coinPair->maker_fees_percent,
                    'taker_fees_percent' => $coinPair->taker_fees_percent,
                ];
            }

            return [
                'success' => true,
                'message' => __('All active coin pairs retrieved successfully'),
                'data' => $formattedCoinPairs
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => __('Error retrieving coin pairs'),
                'data' => []
            ];
        }
    }

    /**
     * Future market overview list with spot-like response structure.
     */
    public function getMarketOverviewTopCoinList($request): array
    {
        try {
            $limit = isset($request->limit) ? (int) $request->limit : 25;
            $offset = isset($request->page) ? (int) $request->page : 1;
            $fiatCurrencyType = $request->currency_type ?? 'USD';
            $search = $request->search;
            $type = (int) ($request->type ?? 3);

            $currencyDetails = CurrencyList::where(['code' => strtoupper($fiatCurrencyType)])->first();
            if (! $currencyDetails) {
                return [
                    'success' => false,
                    'message' => __('Fiat Currency details not found!'),
                    'data' => [],
                ];
            }

            $usdtCoinDetails = Coin::where('coin_type', 'USDT')->first();
            if (! $usdtCoinDetails) {
                return [
                    'success' => false,
                    'message' => __('USDT Coin not found!'),
                    'data' => [],
                ];
            }

            $query = DB::table('future_coin_pairs')
                ->leftJoin('future_prices', 'future_coin_pairs.id', '=', 'future_prices.future_coin_pair_id')
                ->join('coins', 'future_coin_pairs.base_coin_id', '=', 'coins.id')
                ->where('future_coin_pairs.trade_coin_id', $usdtCoinDetails->id)
                ->where('future_coin_pairs.status', STATUS_ACTIVE)
                ->when(filled($search), function ($builder) use ($search) {
                    $builder->where('coins.coin_type', 'like', '%'.$search.'%');
                })
                ->select([
                    'future_coin_pairs.base_coin_id as parent_coin_id',
                    'future_coin_pairs.trade_coin_id as child_coin_id',
                    'future_coin_pairs.id',
                    'future_coin_pairs.code',
                    'coins.coin_icon as coin_icon',
                    'future_coin_pairs.created_at',
                    'coins.coin_type as coin_type',
                    'coins.id as coin_id',
                ])
                ->selectRaw('COALESCE(future_prices.volume_24h_btc, 0) as volume')
                ->selectRaw('COALESCE(future_prices.change_24h, 0) as changed')
                ->selectRaw('COALESCE(future_prices.high_24h, 0) as high')
                ->selectRaw('COALESCE(future_prices.low_24h, 0) as low')
                ->selectRaw('COALESCE(future_prices.market_price, 0) as price');

            $query = match ($type) {
                4 => $query->latest('future_coin_pairs.created_at'),
                default => $query->orderByRaw('COALESCE(future_prices.market_price, 0) DESC'),
            };

            $coinList = $query->paginate($limit, ['*'], 'page', $offset);
            $walletBalances = Wallet::whereIn('coin_id', $coinList->pluck('coin_id')->all())
                ->selectRaw('coin_id, SUM(balance) as total_balance')
                ->groupBy('coin_id')
                ->pluck('total_balance', 'coin_id');

            $coinList->map(function ($item) use ($currencyDetails, $usdtCoinDetails, $walletBalances) {
                $walletBalance = $walletBalances[$item->coin_id] ?? 0;
                $item->total_balance = convertCoinPriceToFiatCurrency(($walletBalance * $item->price), $currencyDetails);
                $item->price = convertCoinPriceToFiatCurrency($item->price, $currencyDetails);
                $item->high = convertCoinPriceToFiatCurrency($item->high, $currencyDetails);
                $item->low = convertCoinPriceToFiatCurrency($item->low, $currencyDetails);
                $item->volume = convert_currency($item->volume, $currencyDetails, $item->coin_type);
                $item->change = $item->changed;

                if (isset($item->coin_icon)) {
                    $item->coin_icon = createImageUrl(IMG_ICON_PATH, $item->coin_icon);
                }

                $item->base_coin_type = $usdtCoinDetails->coin_type;
            });

            return [
                'success' => true,
                'message' => __('Top coin list'),
                'data' => $coinList,
            ];
        } catch (\Throwable $e) {
            errorLogger('FutureTrade getMarketOverviewTopCoinList error: '.$e->getMessage());
            return [
                'success' => false,
                'message' => __('Something went wrong'),
                'data' => [],
            ];
        }
    }
}
