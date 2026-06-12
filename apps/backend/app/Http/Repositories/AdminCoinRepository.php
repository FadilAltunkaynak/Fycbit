<?php

namespace App\Http\Repositories;


use App\Model\Coin;

class AdminCoinRepository extends CommonRepository
{

    public function __construct($model = null)
    {
        parent::__construct($model ?: app(Coin::class));
    }

    public function coinInfo(Coin|int|string $coin): Coin
    {
        return match (true) {
            !empty($coin->id) => $coin,
            is_string($coin) => $this->model::where('coin_type', $coin)->first(),
            default => $this->model::find($coin),
        };
    }

    public function getCoinTypeById(int $id): ?string
    {
        $coin = Coin::find($id);
        if ($coin) {
            return $coin->coin_type;
        }
        return null;
    }

    public function update($where, $update)
    {
        return $this->model::where($where)->update($update);
    }
    public function getCoinListActive()
    {
        return Coin::where('status',STATUS_ACTIVE)->get();
    }

    /**
     * @return Coin->primary active Coin
     */
    public function getPrimaryCoin()
    {
        return Coin::where(['active_status' => 1, 'is_primary' => 1])->first();
    }

    /**
     * @return Coin that are buy able
     */
    public function getBuyableCoin()
    {
        $query = Coin::select('coins.*')->where(['is_buyable' => 1, 'active_status' => 1])->get();
        return $query;
    }

    /**
     * @param $coinId
     * @return Coin details for the by able Coins
     */
    public function getBuyableCoinDetails($coinId)
    {
        $query = Coin::select('coins.*')->where(['is_buyable' => 1, 'active_status' => 1, 'id' => $coinId])->first();
        return $query;
    }

    /**
     * @param $coinId
     * @return Coin Api credentials
     */
    public function getCoinApiCredential($coinId)
    {
        $query = Coin::join('coin_settings', 'coin_settings.coin_id', '=', 'coins.id')
            ->where(['coins.id' => $coinId])
            ->first();
        return $query;
    }

    /**
     * @param $data
     * @return Coin that is created
     */
    public function addCoin($data)
    {
        return Coin::create($data);
    }

    /**
     * @param $coin_id
     * @param $data
     * @return bool that is updated
     */
    public function updateCoin($coin_id, $data)
    {
        //        $service = new UserWalletService();
        //        $service->createAllUserWallet($coin_id);
        return Coin::where(['id' => $coin_id])->update($data);
    }

    /**
     * @param $coinId
     * @return Coin details by $coinId
     */
    public function getCoinDetailsById($coinId)
    {
        return Coin::find($coinId);
    }

    /**
     * @return Coin that are currency
     */
    public function getCurrencyList()
    {
        $query = Coin::select('coin_type', 'name')->where(['is_currency' => 1])->get();

        return $query;
    }

    public function saveCoinByICO($ico_id, $data)
    {
        $check_coin_type = Coin::where('coin_type', $data['coin_type'])->where('ico_id','!=',$data['ico_id'])->get();
        if(count($check_coin_type)>0)
        {
            $response = ['success'=>false, 'message'=>__('Coin type Already exist!')];
            return $response;
        }
        $coin_details = Coin::where('ico_id',$ico_id)->first();

        if(isset($coin_details))
        {
            $coin = Coin::where('ico_id',$ico_id)->first();
            $coin->name = $data['name'];
            $coin->coin_type = $data['coin_type'];
            $coin->network = $data['network'];
            $coin->coin_price = $data['coin_price'];
            $coin->is_deposit = $data['is_deposit'];
            $coin->is_withdrawal = $data['is_withdrawal'];
            $coin->trade_status = $data['trade_status'];
            $coin->is_wallet = $data['is_wallet'];
            $coin->is_buy = $data['is_buy'];
            $coin->status = $data['status'];
            $coin->save();

            $response = ['success'=>true, 'message'=>__('Coin update'),'data'=>$coin];
        }else{
            
            $coin = new Coin;
            $coin->name = $data['name'];
            $coin->coin_type = $data['coin_type'];
            $coin->network = $data['network'];
            $coin->coin_price = $data['coin_price'];
            $coin->is_deposit = $data['is_deposit'];
            $coin->is_withdrawal = $data['is_withdrawal'];
            $coin->trade_status = $data['trade_status'];
            $coin->is_wallet = $data['is_wallet'];
            $coin->is_buy = $data['is_buy'];
            $coin->status = $data['status'];
            $coin->ico_id = $data['ico_id'];
            $coin->save();

            $response = ['success'=>true, 'message'=>__('Coin created'),'data'=>$coin];
        }
        return $response;
    }

}
