<?php

namespace Modules\DemoTrade\Http\Services;

use App\Http\Services\Logger;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\Log;
use Modules\DemoTrade\Entities\CoinPair;
use Modules\DemoTrade\Entities\DemoCoin;
use Modules\DemoTrade\Entities\SelectedCoinPair;
use Modules\DemoTrade\Http\Services\CoinPairRepository;

class CoinPairService extends BaseService
{
    public $model = CoinPair::class;
    public $repository = CoinPairRepository::class;
    public $logger;

    public function __construct()
    {
        parent::__construct($this->model, $this->repository);
        $this->logger = app(Logger::class);
    }

    public function _setDefaultCoinPair($id)
    {
        return SelectedCoinPair::create(['user_id' => $id,'trade_coin_id' => 1, 'base_coin_id' => 2]);
    }

    public function getAllCoinPairs()
    {
        $response = [
            'status' => false,
            'message' =>__('Data not found'),
            'data' => []
        ];
        try {
            $pairs = $this->object->getAllCoinPairs();

            $coinPairs = [];
            if (isset($pairs[0])) {
                foreach ($pairs as $pair) {
                    $coinPairs[] = [
                        "coin_pair_name" => $pair['child_coin_name'].'/'.$pair['parent_coin_name'],
                        "coin_pair" => $pair['child_coin_name'].'_'.$pair['parent_coin_name'],
                        "parent_coin_id" => $pair['parent_coin_id'],
                        "child_coin_id" => $pair['child_coin_id'],
                        "last_price" => $pair['last_price'],
                        "price_change" => $pair['price_change'],
                        "child_coin_name" => $pair['child_coin_name'],
                        "icon" => $pair['icon'],
                        "parent_coin_name" => $pair['parent_coin_name'],
                        "user_id" => $pair['user_id'] ?? '',
                        "balance" => $pair['balance'] ?? 0,
                        "est_balance" => $pair['est_balance'],
                        "is_favorite" => $pair['is_favorite'],
                        "high" => $pair['high'],
                        "low" => $pair['low'],
                        "volume" => $pair['volume'],
                        'pair_name' => $pair['coin_pair_coin']
                    ];
                }
            }
            $response = [
                'status' => true,
                'message' =>__('Data get successfully'),
                'data' => $coinPairs
            ];

            return $response;
        } catch (\Exception $e) {
            Log::info('get all coin pairs exception -> '.$e->getMessage());
            return $response;
        }
    }
    public function getAllCoinPairsData()
    {
        $response = [
            'status' => false,
            'message' =>__('Data not found'),
            'data' => []
        ];
        try {
            $pairs = $this->object->getAllCoinPairs();
            $response = [
                'status' => true,
                'message' =>__('Data get successfully'),
                'data' => $pairs
            ];

            return $response;
        } catch (\Exception $e) {
            storeException('get all coin pairs exception -> ',$e->getMessage());
            return $response;
        }
    }


    public function getCoinDetailsByType($type)
    {
        try {
            if($coins = DemoCoin::where('coin_type', $type)->first()){
                return responseData(true,__("Coin get successfully"),$coins);
            } return responseData(false,__("Coin not found"));
        } catch (\Exception $e) {
            storeException('getCoinDetailsByType', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }
    
    public function saveCoinSetting($request)
    {
        try {
            if($coin = DemoCoin::where('coin_type', $request->coin_type)->first()){
                $data = [
                    'faucet_amount' => $request->faucet_amount ?? 0,
                    'faucet_time' => $request->faucet_time ?? 0,
                    'faucet_min_balance' => $request->faucet_min_balance ?? 0,
                ];
    
                if ($coin->update($data))
                    return responseData(true, __("Coin settings successfully updated"));
                return responseData(false, __("Coin settings updated failed"));
            }
            return responseData(false, __("Coin not found"));
        } catch (\Exception $e) {
            storeException('saveCoinSetting', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

}
