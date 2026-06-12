<?php
namespace Modules\P2P\Http\Service;

use Modules\P2P\Entities\PPaymentTime;
use Modules\P2P\Http\Repository\PaymentTimeRepository;


class PaymentTimeService
{
    private $repo;
    public function __construct() {
        $this->repo = new PaymentTimeRepository();
    }

    public function paymentTimeCreateProcess($request)
    {
        try {
            $successMessage = __('Payment Time created successfully');
            $errorMessage = __('Payment Time created failed');
            if(isset($request->uid)){
                $successMessage = __('Payment Time updated successfully');
            $errorMessage = __('Payment Time updated failed');
            }
            $data = [
                'find' => ['uid' => $request->uid ?? NULL],
                'data' => [
                    'time' => $request->time,
                    'status' => $request->status,
                ]
            ];
            if(!isset($request->uid)) $data['data']['uid'] = pMakeUniqueId();
            $repoResposne = $this->repo->createOrUpdate(PPaymentTime::class, $data);
            if ($repoResposne['success']) $repoResposne['message'] = $successMessage;
            else $repoResposne['message'] = $errorMessage;
            return $repoResposne;
        } catch (\Exception $e) {
            storeException('paymentTimeCreateProcess',$e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function getPaymentsTime()
    {
        try {
            $data = [
                'get' => []
            ];
            $repoResponse = $this->repo->getModelData(PPaymentTime::class, $data);
            return $repoResponse;
        } catch (\Exception $e) {
            storeException('getPaymentsTime', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function findPaymentTime($uid)
    {
        try {
            $data = [
                'where' => ['uid', $uid],
                'first' => []
            ];
            $repoResponse = $this->repo->getModelData(PPaymentTime::class, $data);
            return $repoResponse;
        } catch (\Exception $e) {
            storeException('getPaymentsTime', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function paymentTimeDeleteProcess($request)
    {
        try {
            $data = [
                'where' => ['uid', $request->uid ?? ''],
                'delete' => []
            ];
            $repoResponse = $this->repo->getModelData(PPaymentTime::class, $data);
            if (isset($repoResponse['success']) && $repoResponse['success'])
             $repoResponse['message'] = __("Payment time was successfully deleted");
            else $repoResponse['message'] = __("Payment time deleted failed");
            return $repoResponse;
        } catch (\Exception $e) {
            storeException('getPaymentsTime', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
}
