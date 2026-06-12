<?php
namespace App\Http\Services;


use Carbon\Carbon;
use App\Model\Coin;
use App\Model\Network;
use App\Model\CoinNetwork;
use App\Model\CoinPaymentNetworkFee;
use App\Http\Repositories\CoinPaymentNetworkFeeRepository;

class CoinPaymentNetworkFeeService extends BaseService
{
    public $model = CoinPaymentNetworkFee::class;
    public $repository = CoinPaymentNetworkFeeRepository::class;
    public function __construct()
    {
        parent::__construct($this->model,$this->repository);
    }

    public function  getCoinPaymentNetworkFeeList()
    {
        try{
            $data = $this->object->getCoinPaymentNetworkFeeList();
            $response = ['success' => true, 'message' => __('Coin payment API fee list'), 'data'=>$data];
        }catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("getCoinPaymentNetworkFeeList",$e->getMessage());
        }

        return $response;
    }

    public function CreateOrUpdate()
    {
           try {
                $network = Network::where("base_type", COIN_PAYMENT)->first();
                $coin_network = CoinNetwork::where("network_id", $network?->id)->first();
                if (!$coin_network) return failed(__('No coin found with coin payment network'));

                $api = new CoinPaymentsAPI();
                $rates = $api->GetRates();
                if (!(($rates['error'] ?? '') == 'ok'))
                    return failed($rates['error'] ?? __("Coin Payment Rates Fetched Failed"));

                CoinPaymentNetworkFee::query()->truncate();
                $records = [];
                foreach ($rates['result'] as $type => $row){
                    $records[] = [
                        'coin_type' => $type,
                        'is_fiat' => $row['is_fiat'],
                        'last_update' => date('Y-m-d H:i:s',$row['last_update']),
                        'status' => $row['status'],
                        'tx_fee' => $row['tx_fee'],
                        'rate_btc' => $row['rate_btc'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
                CoinPaymentNetworkFee::insert($records);
                return success(__('CoinPayment Network fees Sync Successfully'));
           } catch (\Exception $e){
                storeException("Sync CoinPayment Network fees : ",$e->getMessage());
                return failed(__('CoinPayment Network fees Sync failed'));
           }
    }

}
