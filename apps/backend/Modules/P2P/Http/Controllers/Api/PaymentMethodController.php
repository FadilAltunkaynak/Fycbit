<?php

namespace Modules\P2P\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Facades\ResponseFacade;
use App\Model\DynamicBank\BankRecord;
use App\Services\BankService\IBankService;
use App\Http\Requests\BankFormCompletionRequest;
use Modules\P2P\Http\Controllers\BaseController;
use Modules\P2P\Http\Service\PaymentMethodService;
use App\Services\BankService\Enums\BankFormAccessType;

class PaymentMethodController extends BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new PaymentMethodService;
    }

    public function createPaymentMethod(BankFormCompletionRequest $request)
    {
        try {
            $response = $this->service->createUserPaymentMethod($request);
            return $this->sendBackResponse($response, ['data' => false], 1);
        } catch (\Exception $e) {
            storeException(false, $e->getMessage());
            return response()->json(responseData(false, __("Something went wrong")));
        }
    }

    public function deletePaymentMethod($id)
    {
        try {
            $response = $this->service->userPaymentMethodDelete($id);
            return $this->sendBackResponse($response, ['data' => false], 1);
        } catch (\Exception $e) {
            storeException(false, $e->getMessage());
            return response()->json(responseData(false, __("Something went wrong")));
        }
    }

    public function adminPaymentMethod()
    {
        try {
            $response = $this->service->getAdminPaymentMethod();
            return $this->sendBackResponse($response, [], 1);
        } catch (\Exception $e) {
            storeException(false, $e->getMessage());
            return response()->json(responseData(false, __("Something went wrong")));
        }
    }

    public function getPaymentMethod(Request $request)
    {
        try {
            $response = $this->service->getPaymentMethod($request);
            return $this->sendBackResponse($response, ['data' => true], 1);
        } catch (\Exception $e) {
            storeException(false, $e->getMessage());
            return response()->json(responseData(false, __("Something went wrong")));
        }
    }

    public function getPaymentMethodDetails($uid)
    {
        try {
            $response = $this->service->getPaymentMethodDetails($uid);
            return $this->sendBackResponse($response, ['data' => true], 1);
        } catch (\Exception $e) {
            storeException(false, $e->getMessage());
            return response()->json(responseData(false, __("Something went wrong")));
        }
    }

    public function getUserPaymentRecord(Request $request, IBankService $service): mixed
    {
        $record = BankRecord::with(['bank_form.paymentMethod'])
            ->where("access", "LIKE", "%" . BankFormAccessType::P2P->value . "%")
            ->where('user_id', authUserId_p2p() ?? '')
            ->paginate($request->per_page ?? 10);

        $record->getCollection()->transform(function ($item) {
            if ($item->bank && isset($item->bank)) {
                $bank = json_decode($item->bank, true);
                if(json_last_error() !== JSON_ERROR_NONE || !is_array($bank)) $bank = [];
                $item->bank = $bank;
            }
            return $item;
        });

        return ResponseFacade::success(__("Payment methods found successfully"),$record)->send();
    }
}
