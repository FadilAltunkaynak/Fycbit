<?php

namespace Modules\P2P\Http\Controllers;

use App\Model\CurrencyList;
use Illuminate\Http\Request;
use Modules\P2P\Http\Service\CurrencyService;
use Modules\P2P\Http\Repository\CurrencyRepository;
use Modules\P2P\Http\Requests\CurrencySettingRequest;

class CurrencyController extends BaseController
{
    private $service; 

    public function __construct()
    {
        $this->service = new CurrencyService();
    }
    public function currencyList(Request $request)
    {
        try {
            if($request->ajax())
            {
                $coins = $this->service->getAllActiveCurrency();
                if (isset($coins['success']) && !$coins['success'])
                    return $this->sendBackResponse($coins, [], 1);
                return datatables()->of($coins['data'])
                    ->addColumn('name', function ($query) {
                        return $query->name;
                    })
                    ->addColumn('code', function ($query) {
                        return $query->code;
                    })
                    ->addColumn('symbol', function ($query) {
                        return $query->symbol;
                    })
                    ->addColumn('rate', function ($query) {
                        return $query->rate;
                    })
                    ->addColumn('action', function ($query) {
                        return ActionButtonForList_p2p($query->id, 'p2pCurrencyEdit','',['id'=>['code' => $query->code],'delete'=>false]);
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }
        } catch (\Exception $e) {
            storeException('coinList p2p',$e->getMessage());
        }
        $data['title'] = __("Fiat Currency");
        return view('p2p::currency.list',$data);
    }

    public function currencyEdit($code)
    {
        $data = [];
        try {
            $data['title'] = __("Currency Setting");
            $data['code'] = $code;
            $response = $this->service->getCurrencyDetailsByCode($code);
            if ($response['success']) $data['setting'] = $response['data'];
        } catch (\Exception $e) {
            storeException('currencyEdit', $e->getMessage());
        }
        return view('p2p::currency.edit', $data);
    }

    public function currencyEditProcess(CurrencySettingRequest $request)
    {
        try {
            $response = $this->service->saveCurrencySetting($request);
            return $this->sendBackResponse($response,['route' => 'p2pCurrencyList']);
        } catch (\Exception $e) {
            storeException('currencyEditProcess', $e->getMessage());
        }
    }

    public function fetchNewCurrency()
    {
        $repo = new CurrencyRepository;
        $currencys = CurrencyList::where("status", STATUS_ACTIVE)->get();
        if(isset($currencys[0])){
            foreach($currencys as $currency)
            {
                $data = [
                    "code" => $currency->code,
                    "minimum_price" => 1,
                    "maximum_price" => 100,
                    "trade_status" => STATUS_ACTIVE
                ];
                $repo->saveCurrencySetting((Object)$data);
            }
        }
        return $this->sendBackResponse(['success' => true, "message" => __("Currency fetched successfully")]);
    }
}
