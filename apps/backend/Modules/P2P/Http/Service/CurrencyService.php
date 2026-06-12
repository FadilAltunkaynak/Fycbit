<?php
namespace Modules\P2P\Http\Service;

use App\Model\CurrencyList;
use Illuminate\Support\Facades\DB;
use Modules\P2P\Entities\PCurrencySetting;
use Modules\P2P\Http\Repository\CurrencyRepository;


class CurrencyService
{
    private $repo;

    public function __construct() {
        $this->repo = new CurrencyRepository();
    }

    public function getAllActiveCurrency()
    {
        try {
            $currency = DB::table('currency_lists')->select(DB::raw("currency_lists.* , p_currency_settings.trade_status as p_status"))
            ->join('p_currency_settings',['p_currency_settings.currency_code' => 'currency_lists.code'])
            ->get();
            return responseData(true, __("coin found"), $currency);
        } catch (\Exception $e) {
            storeException('getAllActiveCoin', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    public function getCurrencyDetailsByCode($code)
    {
        try {
            return $this->repo->getCurrencyDetailsByCode($code);
        } catch (\Exception $e) {
            storeException('getCurrencyDetailsByCode', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }
    
    public function saveCurrencySetting($request)
    {
        try {
            return $this->repo->saveCurrencySetting($request);
        } catch (\Exception $e) {
            storeException('saveCurrencySetting', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }
}