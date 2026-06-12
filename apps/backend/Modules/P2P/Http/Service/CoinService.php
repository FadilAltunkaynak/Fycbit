<?php
namespace Modules\P2P\Http\Service;

use App\Model\Coin;
use Illuminate\Support\Facades\DB;
use Modules\P2P\Entities\PCoinSetting;
use Modules\P2P\Http\Repository\CoinRepository;

class CoinService
{
    private $repo;

    public function __construct() {
        $this->repo = new CoinRepository();
    }

    public function getAllActiveCoin()
    {
        try {
            $coins = DB::table('coins')->select(DB::raw("coins.* , p_coin_settings.trade_status as p_status"))
                     ->join('p_coin_settings',['p_coin_settings.coin_type' => 'coins.coin_type'])
                     ->get();
            return responseData(true, __("coin found"), $coins); // $this->repo->getModelData(Coin::class, $data);
        } catch (\Exception $e) {
            storeException('getAllActiveCoin', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    public function getCoinDetailsByType($type)
    {
        try {
            return $this->repo->getCoinDetailsByType($type);
        } catch (\Exception $e) {
            storeException('getCoinDetailsByType', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }
    
    public function saveCoinSetting($request)
    {
        try {
            return $this->repo->saveCoinSetting($request);
        } catch (\Exception $e) {
            storeException('saveCoinSetting', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }
}