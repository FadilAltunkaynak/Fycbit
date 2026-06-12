<?php
namespace Modules\P2P\Http\Repository;

use Modules\P2P\Entities\PCoinSetting;

class CoinRepository extends BaseRepository
{
    public function __construct() {
    }

    public function getCoinDetailsByType($type)
    {
        try {
            $data = [
                'where' => ['coin_type',$type],
                'first' => []
            ];
            return  $this->getModelData(PCoinSetting::class, $data);
        } catch (\Exception $e) {
            storeException('getCoinDetailsByType',$e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function saveCoinSetting($data)
    {
        try{
            $find = ['coin_type' => $data->coin_type];
            $data = [
                'minimum_price' => $data->minimum_price ?? "",
                'maximum_price' => $data->maximum_price ?? "",
                'buy_fees' => $data->buy_fees ?? "",
                'sell_fees' => $data->sell_fees ?? "",
                'trade_status'  => $data->trade_status ?? 0,
            ];

            $success = PCoinSetting::updateOrCreate($find,$data);
            if ($success)
                return responseData(true, __("Coin settings successfully updated"));
            return responseData(false, __("Coin settings updated failed"));
        } catch (\Exception $e) {
            storeException('saveCoinSetting repo',$e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
}