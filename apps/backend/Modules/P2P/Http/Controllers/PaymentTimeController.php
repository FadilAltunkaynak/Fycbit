<?php

namespace Modules\P2P\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\P2P\Http\Service\PaymentTimeService;
use Modules\P2P\Http\Requests\PaymentTimeCreateRequest;
use Modules\P2P\Http\Requests\PaymentTimeDeleteRequest;

class PaymentTimeController extends BaseController
{
    private $service;

    public function __construct(){
        $this->service = new PaymentTimeService();
    }

    public function paymentTime(Request $request)
    {
        $data = [];
        try {
            if($request->ajax())
            {
                $times = $this->service->getPaymentsTime();
                if (isset($tiems['success']) && !$times['success'])
                    return $this->sendBackResponse($times, [], 1);
                return datatables()->of($times['data'])
                    ->addColumn('time', function ($query) {
                        return $query->time;
                    })
                    ->addColumn('status', function ($query) {
                        return statusOnOffAction_p2p($query->status);
                    })
                    ->addColumn('action', function ($query) {
                        return ActionButtonForList_p2p($query->uid, 'p2pPaymentTimeCreatePage','p2pPaymentTimeDeletePage',['id'=>['uid' => $query->uid]]);
                    })
                    ->rawColumns(['status','action'])
                    ->make(true);

            }
        } catch (\Exception $e) {
            storeException('p2p paymentTime', $e->getMessage());
        }
        $data['title'] = __("Payment Time");
        return view('p2p::payment_time.list',$data);
    }

    public function paymentTimeCreatePage($uid = null)
    {
        $data = [];
        try {
            $data['title'] = __("Create Payment Time");
            if($uid){
                $find = $this->service->findPaymentTime($uid);
                if (!$find['success'])
                    return $this->sendBackResponse($find);
                if($find['success']) $data['time'] = $find['data'];
                $data['title'] = __("Updata Payment Time");
            }
        } catch (\Exception $e) {
            storeException('paymentTimeCreate', $e->getMessage());
        }
        return view('p2p::payment_time.addEdit',$data);
    }

    public function paymentTimeCreate(PaymentTimeCreateRequest $request)
    {
        try {
            $response = $this->service->paymentTimeCreateProcess($request);
            return $this->sendBackResponse($response,['route' => 'p2pPaymentTime']);
        } catch (\Exception $e) {
            storeException('paymentTimeCreate', $e->getMessage());
            return $this->sendBackResponse(responseData(false,__('Something went wrong')));
        }
    }

    public function paymentTimeDeletePage(PaymentTimeDeleteRequest $request)
    {
        try {
            $response = $this->service->paymentTimeDeleteProcess($request);
            return $this->sendBackResponse($response,['route' => 'p2pPaymentTime']);
        } catch (\Exception $e) {
            storeException('paymentTimeCreate', $e->getMessage());
            return $this->sendBackResponse(responseData(false,__('Something went wrong')));
        }
    }
}
