<?php
namespace Modules\DemoTrade\Http\Services;

use App\User;
use App\Model\Coin;
use App\Model\CoinSetting;
use Illuminate\Support\Carbon;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Hash;
use Modules\DemoTrade\Entities\Wallet;
use Modules\DemoTrade\Entities\DemoCoin;
use Modules\IcoLaunchpad\Entities\IcoToken;
use Modules\DemoTrade\Entities\FaucetHistory;
use App\Http\Repositories\AdminCoinRepository;
use Modules\IcoLaunchpad\Entities\IcoPhaseInfo;
use Modules\IcoLaunchpad\Entities\TokenBuyHistory;

class CoinService extends BaseService {

    public $model = Coin::class;
    public $repository = AdminCoinRepository::class;

    public function __construct(){
        parent::__construct($this->model,$this->repository);
    }

    public function getCoin($data){
        $object = $this->object->getDocs($data);

        if (empty($object)) {
            return null;
        }

        return $object;
    }

    public function getWalletBallance($request)
    {
        try {
            if($coin = DemoCoin::where('coin_type', $request->coin_type)->first()) {
                if($wallet = Wallet::where(['user_id' => getUserId(), 'coin_type' => $coin->coin_type])->first()){

                    if($wallet->balance >= $coin->faucet_min_balance)
                            return responseData(false, __("Your wallet have minimal amount of coins available"));

                    if($faucet = FaucetHistory::where(['user_id' => $wallet->user_id, 'coin_type' => $coin->coin_type])->latest()->first()){
                        $logic = Carbon::now()->gte(Carbon::parse($faucet->next_time));
                        if($logic){
                            $wallet->increment('balance', $coin->faucet_amount);
                            if($this->createFaucetHistory($coin, $wallet))
                                return responseData(true, __("Balance added successfully"),$wallet);
                            return responseData(false, __("Balance added failed"));
                        } return responseData(false, __("You cannot claim coins before :time hours",[ 'time' => $coin->faucet_time ])); 
                    }

                    $wallet->increment('balance', $coin->faucet_amount);
                    if($this->createFaucetHistory($coin, $wallet))
                        return responseData(true, __("Balance added successfully"),$wallet);
                    return responseData(false, __("Balance added failed"));

                } return responseData(false, __("Wallet not found"));
            } return responseData(false, __("Coin not found"));
        } catch (\Exception $e) {
            storeException('getWalletBallance faucet',$e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    private function createFaucetHistory($coin, $wallet){
        $history = FaucetHistory::create([
            'user_id' => $wallet->user_id,
            'wallet_id' => $wallet->id,
            'coin_id' => $wallet->coin_id,
            'coin_type' => $wallet->coin_type,
            'amount' => $coin->faucet_amount ?? 0,
            'next_time' => Carbon::now()->addHours($coin->faucet_time ?? 72),
        ]);
        return $history;
    }
}
