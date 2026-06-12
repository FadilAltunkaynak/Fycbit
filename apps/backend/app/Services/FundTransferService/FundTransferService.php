<?php

namespace App\Services\FundTransferService;

use App\Enums\FundEnum;
use App\Model\Coin;
use App\Services\FundTransferService\DataObject\FundCheckerQueryParams;
use App\Services\FundTransferService\DataObject\FundManageParams;
use App\Services\FundTransferService\DataObject\FundTransferQueryParams;
use App\Services\FundTransferService\DataObject\GetWalletBalanceParams;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FundTransferService
{
    protected array $config;

    public function __construct()
    {
        $this->config = config("fund_transfer");
    }

    /**
     * Get Coin List By Fund For Transfer Fund Page
     * @param \App\Enums\FundEnum $fundType
     * @param \App\Services\FundTransferService\DataObject\FundTransferQueryParams $params
     * @return \Illuminate\Support\Collection<Coin>
     */
    public function getCoinsByFund(FundEnum $fundType, FundTransferQueryParams $params): Collection
    {
        return (new $this->config['coin_query'][$fundType->value])($params);
    }

    /**
     * Check Has Wallet
     * Create Wallet If Not Found
     * Check Wallet Balance , return null on empty balance
     * return Wallet Model
     *
     * @param \App\Services\FundTransferService\DataObject\FundCheckerQueryParams $params
     * @return Model
     */
    public function checkWalletAndBalance(FundEnum $fundType, FundCheckerQueryParams $params)
    {
        return (new $this->config['fund_check'][$fundType->value])($params);
    }

    /**
     * Highly recommended to use this method in db transaction ( used lockForUpdate )
     * 
     * Deduct amount from wallet
     * 
     * @param \App\Services\FundTransferService\DataObject\FundManageParams $params
     * @return bool
     */
    public function takeFundFrom(FundEnum $fundType, FundManageParams $params): bool
    {
        if (!isset($this->config['fund_taker'][$fundType->value])) {
            return false;
        }

        return (new $this->config['fund_taker'][$fundType->value])($params);
    }

    /**
     * Highly recommended to use this method in db transaction ( used lockForUpdate )
     * 
     * Increment amount from wallet
     * 
     * @param \App\Services\FundTransferService\DataObject\FundManageParams $params
     * @return bool
     */
    public function giveFundTo(FundEnum $fundType, FundManageParams $params): bool
    {
        if (!isset($this->config['fund_giver'][$fundType->value])) {
            return false;
        }

        return (new $this->config['fund_giver'][$fundType->value])($params);
    }

    /**
     * Get Wallet Balance By Fund Type
     * 
     * @param FundEnum $fundType
     * @param GetWalletBalanceParams $params
     * @return float|string
     */
    public function getWalletBalance(FundEnum $fundType, GetWalletBalanceParams $params): float|string
    {
        if (!isset($this->config['wallet_balance'][$fundType->value])) {
            return 0;
        }

        return (new $this->config['wallet_balance'][$fundType->value])($params);
    }
}