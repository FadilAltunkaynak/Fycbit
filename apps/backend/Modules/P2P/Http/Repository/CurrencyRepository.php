<?php
namespace Modules\P2P\Http\Repository;

use Modules\P2P\Entities\PCurrencySetting;

class CurrencyRepository extends BaseRepository
{
    public function __construct() {
    }

    public function getCurrencyDetailsByCode($code)
    {
        try {
            $data = [
                'where' => ['currency_code',$code],
                'first' => []
            ];
            return  $this->getModelData(PCurrencySetting::class, $data);
        } catch (\Exception $e) {
            storeException('getCurrencyDetailsByCode',$e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function saveCurrencySetting($data)
    {
        try{
            $find = ['currency_code' => $data->code];
            $data = [
                'minimum_price' => $data->minimum_price ?? "",
                'maximum_price' => $data->maximum_price ?? "",
                'trade_status'  => $data->trade_status ?? 0,
            ];

            $success = PCurrencySetting::updateOrCreate($find,$data);
            if($success) return responseData(true, __("Currency setting updated successfully"));
            return responseData(false, __("Currency setting updated failed"));
        } catch (\Exception $e) {
            storeException('saveCurrencySetting p2p repo',$e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
}