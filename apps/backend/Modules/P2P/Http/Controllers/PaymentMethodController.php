<?php

namespace Modules\P2P\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\BankService\IBankService;
use Modules\P2P\Http\Service\PaymentMethodService;
use Modules\P2P\Http\Requests\PaymentMethodRequest;
use App\Services\BankService\Enums\BankFormFieldType;
use App\Services\BankService\Enums\BankFormAccessType;
use Modules\P2P\Http\Requests\PaymentMethodDeleteRequest;

class PaymentMethodController extends BaseController
{
    private PaymentMethodService $service;
    public function __construct(){
        $this->service = new PaymentMethodService();
    }

    public function paymentMethodList(Request $request)
    {
        $data = [];
        try {
            if($request->ajax())
            {
                $payments = $this->service->getAllPaymentMethod();
                if (isset($payments['success']) && !$payments['success'])
                    return $this->sendBackResponse($payments, [], 1);
                return datatables()->of($payments['data'])
                    ->addColumn('name', function ($query) {
                        return $query->name;
                    })
                    ->addColumn('logo', function ($query) {
                        if(!empty($query->logo))
                        return '<img height="50px" src="'. asset(PAYMENT_METHOD_LOGO_PATH.$query->logo).'">';
                        return '<img height="50px" src="'. asset("assets/img/placeholder-image.png").'">';
                    })
                    ->addColumn('payment_type', function ($query) {
                        return $query?->bank_form?->title ?? __("Not found");
                    })
                    ->addColumn('status', function ($query) {
                        return statusOnOffAction_p2p($query->status);
                    })
                    ->addColumn('created_at', function ($query) {
                        return $query->created_at;
                    })
                    ->addColumn('action', function ($query) {
                        return ActionButtonForList_p2p($query->uid, 'p2pPaymentMethodCreate','paymentMethodDelete',['id'=>['uid' => $query->uid]]);
                    })
                    ->rawColumns(['logo','status','action'])
                    ->make(true);
            }
        } catch (\Exception $e) {
            storeException('paymentMethodList', $e->getMessage());
        }
        $data['title'] = __("Payment Methods");
        return view('p2p::payment_method.list',$data);
    }

    public function paymentMethodCreate(IBankService $bankService, $uid = null)
    {
        $data = [];
        try {
            if($uid)
            {
                $data['uid'] = $uid;
                $payment_method = $this->service->paymentMethodFind($uid);
                if (!$payment_method['success']) return $this->sendBackResponse($payment_method);
                
                $payment_method['data']->country = json_encode(explode('|', $payment_method['data']->country));
                $payment_method['data']->logo = asset(PAYMENT_METHOD_LOGO_PATH.$payment_method['data']->logo);
                $data['payment'] = $payment_method['data'];
            }
            $country = $this->service->getCountry();
            $data['forms'] = $bankService->getFormsByAccess(BankFormAccessType::P2P->value)['data'] ?? null;
            if(isset($country['success']) && $country['success']) $data['country'] = $country['data'];
        } catch (\Exception $e) {
            storeException('paymentMethodList', $e->getMessage());
        }
        $data['title'] = __("Payment Methods");
        return view('p2p::payment_method.addEdit',$data);
    }

    public function paymentMethodCreateProcess(PaymentMethodRequest $request)
    {
        try {
            $response = $this->service->paymentMethodSave($request);
            return $this->sendBackResponse($response,['route' => 'paymentMethodList']);
        } catch (\Exception $e) {
            storeException(false, __("Something went wrong"));
        }
    }

    public function paymentMethodDelete(PaymentMethodDeleteRequest $request)
    {
        try {
            $response = $this->service->paymentMethodDelete($request);
            return $this->sendBackResponse($response,['route' => 'paymentMethodList']);
        } catch (\Exception $e) {
            storeException(false, __("Something went wrong"));
        }
    }
}
