<?php
namespace Modules\P2P\Http\Service;

use App\Model\DynamicBank\BankRecord;
use Modules\P2P\Entities\PPaymentMethod;
use App\Services\BankService\IBankService;
use App\Services\BankService\Enums\BankFormAccessType;
use Modules\P2P\Http\Repository\PaymentMethodRepository;

class PaymentMethodService
{
    private $repo;

    public function __construct()
    {
        $this->repo = new PaymentMethodRepository();
    }

    public function getCountry()
    {
        try {
            return $this->repo->getCountry();
        } catch (\Exception $e) {
            storeException('getCountry p2p', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function paymentMethodFind($uid)
    {
        try {
            $data = [
                'where' => ['uid', $uid],
                'first' => []
            ];
            $paymentMethod = $this->repo->getModelData(PPaymentMethod::class,$data);
            if ($paymentMethod['success']) $paymentMethod['message'] = __('Payment method found successfully');
            else $paymentMethod['message'] = __('Payment method not found');
            return $paymentMethod;
        } catch (\Exception $e) {
            storeException('paymentMethodFind p2p', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function getAllPaymentMethod()
    {
        try {
            $paymentMethod = $this->repo->getModelData(PPaymentMethod::class,['with' => ['bank_form']]);
            if ($paymentMethod['success']) $paymentMethod['message'] = __('Payment methods found successfully');
            else $paymentMethod['message'] = __('Payment methods not found');
            return $paymentMethod;
        } catch (\Exception $e) {
            storeException('getAllPaymentMethod p2p', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function paymentMethodDelete($request)
    {
        try {
            $query = [
                'where' => ['uid', $request->id],
                'first' => []
            ];
            $paymentMethod = $this->repo->getModelData(PPaymentMethod::class,$query);
            if($paymentMethod['success']){
                uploadFilep2p('', PAYMENT_METHOD_LOGO_PATH, $paymentMethod['data']->logo ?? '');
            }
            $query = [
                'where' => ['uid', $request->id],
                'delete' => []
            ];
            $paymentMethod = $this->repo->getModelData(PPaymentMethod::class,$query);
            if ($paymentMethod['success']) $paymentMethod['message'] = __('Payment methods deleted successfully');
            else $paymentMethod['message'] = __('Payment methods deleted failed');
            return $paymentMethod;
        } catch (\Exception $e) {
            storeException('paymentMethodDelete p2p', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function userPaymentMethodDelete($id)
    {
        try {
            $query = [
                'getUserPaymentMethod' => [authUserId_p2p() ?? 0],
                'where' => ['id', $id],
                'delete' => []
            ];
            $paymentMethod = $this->repo->getModelData(BankRecord::class,$query);
            if ($paymentMethod['success']) $paymentMethod['message'] = __('Payment methods deleted successfully');
            else $paymentMethod['message'] = __('Payment methods deleted failed');
            return $paymentMethod;
        } catch (\Exception $e) {
            storeException('userPaymentMethodDelete p2p', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function getPaymentMethod($request)
    {
        try {
            $query = [
                'getUserPaymentMethod' => [authUserId_p2p() ?? 0],
                'paginate' => [$request->per_page ?? 5]
            ];
            $paymentMethod = $this->repo->getModelData(BankRecord::class,$query);

            if ($paymentMethod['success']) {
                $paymentMethod['message'] = __('Payment methods found successfully');
                $paymentMethod['data']->getCollection()->transform(function ($item) {
                    if ($item->bank && isset($item->bank)) {
                        $bank = json_decode($item->bank, true);
                        if(json_last_error() !== JSON_ERROR_NONE || !is_array($bank)) $bank = [];
                        $item->bank = $bank;
                    }
                    return $item;
                });
            }
            else $paymentMethod['message'] = __('Payment methods not found');
            return $paymentMethod;
        } catch (\Exception $e) {
            storeException('getPaymentMethod p2p', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function paymentMethodSave($request)
    {
        try {
            $successRes = __("Payment method created successfully");
            $errorRes = __("Payment method failed to create");
            if(isset($request->uid)){
                $successRes = __("Payment method updated successfully");
                $errorRes = __("Payment method failed to updated");
            }
            $data = [
                'find' => [ 'uid' => $request->uid ?? '' ],
                'data' => [
                    'name' => $request->name,
                    'country' => implode('|',$request->country),
                    'status' => $request->status,
                    'note' => $request->note ?? '',
                ]
            ];
            if ($request->hasFile('logo')) {
                if(isset($request->uid)){
                    $paymentData = $this->paymentMethodFind($request->uid);
                    if($paymentData['success']){
                        $data['data']['logo'] = uploadFilep2p(
                            $request->file('logo'),
                            PAYMENT_METHOD_LOGO_PATH,
                            $paymentData['data']->logo ?? ''
                        );
                    }
                }
                else $data['data']['logo'] = uploadFilep2p($request->file('logo'), PAYMENT_METHOD_LOGO_PATH);
            }
            if(!isset($request->uid)) {
                $has = PPaymentMethod::where('payment_type', $request->payment_type)->exists();
                if ($has) return responseData(false, __("Payment method already exists with same bank form"));

                $data['data']['uid'] = pMakeUniqueId();
                $data['data']['payment_type'] = $request->payment_type;
            }
            $response = $this->repo->paymentMethodSave($data);
            if ($response['success']) $response['message'] = $successRes;
            else $response['message'] = $errorRes;
            return $response;
        } catch (\Exception $e) {
            storeException('getCountry p2p', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function getAdminPaymentMethod()
    {
        try {
            if(authUserId_p2p()){
                $query = [
                    "with" => [["bank_form","bank_form.fields"]],
                    "where" => [ "status", STATUS_ACTIVE ],
                    "get" => [['uid', 'name', "payment_type"]]
                ];
                $result = $this->repo->getModelData(PPaymentMethod::class,$query);
                if(isset($result['success']) && !$result['success'])
                $result['message'] = __("Admin Payment Methods get faild");
                $result['message'] = __("Admin Payment Methods get successfully");
                return $result;
            }
            return responseData(false, __("Something went wrong"));
        } catch (\Exception $e) {
            storeException("getAdminPaymentMethod", $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function getPaymentMethodDetails($uid)
    {
        try {
            $paymentMethod = BankRecord::getBankFormRelation()->where('id', $uid)->first();
            if($paymentMethod){
                if ($paymentMethod->bank && isset($paymentMethod->bank)) {
                    $bank = json_decode($paymentMethod->bank, true);
                    if(json_last_error() !== JSON_ERROR_NONE || !is_array($bank)) $bank = [];
                    $paymentMethod->bank = $bank;
                }
               return responseData(true, __("Payment Method found successfully"), $paymentMethod);
            }
            return responseData(false, __("Payment Method not found"));
        } catch (\Exception $e) {
            storeException("getPaymentMethodDetails", $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function createUserPaymentMethod($request): array
    {
        try {
            if($request?->id){
                $bankRecord = BankRecord::where("id",$request?->id ?? "")
                    ->where("access", "LIKE", "%" . BankFormAccessType::P2P->value . "%")
                    ->first();
                if(!$bankRecord) return failed(__("Payment method not found"));
            }

            $request->merge([
                "accessType" => BankFormAccessType::P2P->value
            ]);

            $bankService = app(IBankService::class);
            return $bankService->saveBank($request);
        } catch (\Exception $e) {
            storeException('createUserPaymentMethod p2p', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
}
