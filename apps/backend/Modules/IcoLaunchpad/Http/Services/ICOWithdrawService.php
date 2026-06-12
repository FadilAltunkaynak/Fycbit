<?php

namespace Modules\IcoLaunchpad\Http\Services;

use TokenBuyEarn;
use App\Model\Coin;
use App\Model\CurrencyList;
use Modules\IcoLaunchpad\Http\Repositories\ICOWithdrawRepository;

class ICOWithdrawService
{
    private $repo;
    public function __construct()
    {
        $this->repo = new ICOWithdrawRepository();
    }

    public function getTokenEarnigs()
    {
        try {
            $earns = $this->repo->getEarnsData();
            $currency_type = (object)getCurrencyType();
            $currency = CurrencyList::whereStatus(STATUS_ACTIVE)->get();
            $coins = Coin::whereStatus(STATUS_ACTIVE)->get(['id', 'coin_type']);
            return responseData(true, __("Earns get successfully"), [
                'earns' => $earns,
                'currency_types' => $currency_type,
                'currencys' => $currency,
                'coins' => $coins,
            ]);
        } catch (\Exception $e) {
            storeException('getTokenEarnigs ex', $e->getMessage());
            return responseData(false, __("Earns get failed"));
        }
    }

    public function getTokenWithdrawlPrice($request)
    {
        try {
            $default_currency = $this->repo->getDataOfTokenEarns('currency');
            if (!$default_currency['success']) return responseData(false, __("You don't have any balance to withdraw"));
            $currency = "USDT";
            $currency2 = null;
            if ($request->currency_type == CRYPTO_TYPE) {
                $currency = $request->currency_to;
            } else {
                $currency2 = $request->currency_to;
            }
            $price = convert_currency($request->amount, $currency, $default_currency['data'], $currency2);
            return responseData(
                true,
                __("Price get successfully"),
                [
                    'currency_to' => $request->currency_to,
                    'currency_from' => $default_currency['data'],
                    'amount' => $request->amount,
                    'price' => $price
                ]
            );
        } catch (\Exception $e) {
            storeException('getTokenWithdrawlPrice ex', $e->getMessage());
            return responseData(false, __("Price get failed"));
        }
    }

    private function checkEarningBlance($request)
    {
        $earn = $this->getTokenEarnigs();
        if (!$earn['success']) {
            return responseData(false, __("You don't have any balance to withdraw"));
        }
        if (isset($earn['data']['earns']) && $earn['data']['earns']->available < $request->amount) {
            return responseData(false, __("Insufficient balance to withdrawal"));
        } else if (isset($earn['data']['earns']) && $earn['data']['earns']->available >= $request->amount) {
            return responseData(true, __("You have enough balance to withdrawal"));
        }
        return responseData(false, __("You do not have enough balance to withdrawal"));
    }

    public function getTokenWithdrawlRequest($request)
    {
        try {
            if(!isset($request->payment_details) && $request->currency_type == FIAT_TYPE)
            return responseData(false, __("Payment details is required"));

            $earn = $this->checkEarningBlance($request);
            if (!$earn['success'])
                return $earn;
            $data = $this->getTokenWithdrawlPrice($request);
            if (!$data['success']) return responseData(false, __("Withdrawal request submit failed"));
            $withdrawal = $this->repo->makeWithdrawalRequest($request, $data);
            if (!empty($withdrawal))
                return responseData(true, __("Withdrawal request submit successfully"));
            else
                return responseData(false, __("Withdrawal request submit failed"));
        } catch (\Exception $e) {
            storeException('getTokenWithdrawlRequest ex', $e->getMessage());
            return responseData(false, __("Withdrawal request failed"));
        }
    }

    public function IcoWithdrawAcceptRequest($id)
    {
        try {
            $response = $this->repo->IcoWithdrawAcceptRequest($id);
            if ($response)
                return responseData(true, __("Withdrawal request accepted"));
            return responseData(false, __("Withdrawal request not accepted"));
        } catch (\Exception $e) {
            storeException('IcoWithdrawAcceptRequest ex', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
    public function IcoFiatWithdrawAcceptRequest($request)
    {
        try {
            $response = $this->repo->icoFiatWithdrawalAdminAccept($request);
            if ($response)
                return responseData(true, __("Withdrawal request accepted"));
            return responseData(false, __("Withdrawal request not accepted"));
        } catch (\Exception $e) {
            storeException('IcoFiatWithdrawAcceptRequest ex', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function IcoWithdrawRejectRequest($id)
    {
        try {
            $response = $this->repo->IcoWithdrawRejectRequest($id);
            if ($response)
                return responseData(true, __("Withdrawal request rejected"));
            return responseData(false, __("Withdrawal request not rejected"));
        } catch (\Exception $e) {
            storeException('IcoWithdrawAcceptRequest ex', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
    
    public function getTokenWithdrawalList($request)
    {
        try {
            return $this->repo->getTokenWithdrawalList($request);
        } catch (\Exception $e) {
            storeException('IcoWithdrawAcceptRequest ex', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
}
