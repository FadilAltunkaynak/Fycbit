<?php

namespace Modules\FutureTrade\Http\Controllers\Api;

use App\Facades\ResponseFacade;
use App\Model\AdminSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\FutureTrade\Repositories\CoinPairRepository\CoinPairRepository;
use Modules\FutureTrade\Services\CoinPairService\CoinPairService;
use Modules\FutureTrade\Services\CoinPairService\CoinPairDetailService;

class FutureCoinPairController extends Controller
{
    public function getCoinPairList(CoinPairService $service,  ?string $limit = null): mixed
    {
        $data = $service->getCoinPairList($limit);
        return ResponseFacade::result($data)->send();
    }

    /**
     * Get default coin pair code from admin settings
     * Returns the default coin pair configured in the database
     */
    public function getDefaultCoinPair(): mixed
    {
        try {
            $defaultCoinPairId = AdminSetting::where('slug', 'future_trade_default_coin_pair')
                ->value('value');

            if (!$defaultCoinPairId) {
                return ResponseFacade::failed(__('Default coin pair not configured'))->send();
            }

            $coinPair = CoinPairRepository::getCoinPairById($defaultCoinPairId);

            if (!$coinPair) {
                return ResponseFacade::failed(__('Default coin pair not found'))->send();
            }

            $data = [
                'uid' => $coinPair->uid,
                'code' => $coinPair->code,
            ];

            return ResponseFacade::success($data, __('Default coin pair retrieved successfully'))->send();
        } catch (\Exception $e) {
            errorLogger('Error fetching default coin pair: ' . $e->getMessage());
            return ResponseFacade::failed(__('Error retrieving default coin pair'))->send();
        }
    }

    /**
     * Get Coin Pair Details with 24-hour Change Data
     * 
     * @param Request $request - Must contain 'code' parameter
     * @param CoinPairDetailService $service
     * @return mixed
     */
    public function getCoinPairDetailsByCode(Request $request, CoinPairDetailService $service): mixed
    {
        if (!$request->filled('code')) {
            return ResponseFacade::failed(__('Coin code is required'))->send();
        }

        $code = $request->input('code');
        $response = $service->getCoinPairDetailsByCoinCode($code);

        return ResponseFacade::result($response)->send();
    }

    /**
     * Get Multiple Coin Pairs Details with 24-hour Change Data
     * 
     * @param Request $request - Must contain 'codes' parameter (array of codes)
     * @param CoinPairDetailService $service
     * @return mixed
     */
    public function getMultipleCoinPairsDetails(Request $request, CoinPairDetailService $service): mixed
    {
        if (!$request->filled('codes')) {
            return ResponseFacade::failed(__('At least one coin code is required'))->send();
        }

        $codes = is_array($request->input('codes')) 
            ? $request->input('codes') 
            : explode(',', $request->input('codes'));

        $response = $service->getMultipleCoinPairDetails($codes);

        return ResponseFacade::result($response)->send();
    }

    /**
     * Get All Active Coin Pairs with 24-hour Change Data
     * 
     * @param CoinPairDetailService $service
     * @return mixed
     */
    public function getAllActiveCoinPairs(CoinPairDetailService $service): mixed
    {
        $response = $service->getAllActiveCoinPairsWithChangeData();

        return ResponseFacade::result($response)->send();
    }

    /**
     * Get Future Market Overview Top Coin List (spot-like response shape)
     */
    public function getMarketOverviewTopCoinList(Request $request, CoinPairDetailService $service): mixed
    {
        $response = $service->getMarketOverviewTopCoinList($request);
        return ResponseFacade::result($response)->send();
    }
}
