<?php
namespace Modules\P2P\Http\Service;

use App\User;
use App\Model\Faq;
use App\Model\CountryList;
use App\Model\AdminSetting;
use Modules\P2P\Entities\PBuy;
use Modules\P2P\Entities\PSell;
use Modules\P2P\Entities\POrder;
use App\Model\VerificationDetails;
use Illuminate\Support\Facades\DB;
use App\Model\ThirdPartyKycDetails;
use Modules\P2P\Entities\P2PWallet;
use App\Model\DynamicBank\BankRecord;
use Modules\P2P\Entities\PPaymentTime;
use Modules\P2P\Entities\PPaymentMethod;
use Modules\P2P\Http\Repository\AdsRepository;
use App\Services\BankService\Enums\BankFormAccessType;

class AdsService
{
    private $repo;

    public function __construct()
    {
        $this->repo = new AdsRepository();
    }

    public function createAds($request)
    {

        try {
            $chec_kyc = $this->checkValidation();
            if(isset($chec_kyc['success']) && !$chec_kyc['success']) return $chec_kyc;
            $data = ['find' => [ 'uid' => $request->uid ?? '' ]];
            $data['data'] = [];
            $successRes = __("Ads created successfully");
            $errorRes = __("Ads failed to create");
            if(isset($request->uid)){
                $successRes = __("Ads updated successfully");
                $errorRes = __("Ads failed to updated");
            }else{
                $data['data']['uid'] = pMakeUniqueId();
                $data['data']['user_id'] = authUserId_p2p();
            }
            $data['data']  = array_merge($data['data'], [
                'coin_type' => $request->coin_type,
                'currency' => $request->fiat_type,
                'price_type' => $request->price_type,
                'price' => $request->price,
                'price_rate' => $request->price_rate,
                'amount' => $request->amount,
                'available' => $request->amount,
                'minimum_trade_size' => $request->min_limit,
                'maximum_trade_size' => $request->max_limit,
                'terms' => $request->terms,
                'auto_reply' => $request->auto_reply,
                'country' => $this->getCountry($request->countrys),
                'ip' => $request->ip()
            ]);
            if(isset($request->payment_methods)){
                $data['data']['payment_method'] = $request->payment_methods;
                $userPaymentMethods = explode( "," ,$request->payment_methods);
                if(isset($userPaymentMethods[0])){
                    $payments = '';
                    foreach($userPaymentMethods as $payment){
                        $adminPayment = BankRecord::with('paymentMethod')->where('id',$payment)
                            ->where("access", "LIKE", "%" . BankFormAccessType::P2P->value . "%")
                            ->where('user_id', authUserId_p2p() ?? '')
                            ->first();
                        if($adminPayment){
                            if($payments != '') $payments .= ",";
                            $payments .= $adminPayment?->paymentMethod?->uid;
                        }else{
                            return responseData(false, __("Payment method not exist !!"));
                        }
                    }
                    $data['data']['admin_payment_method'] = $payments;
                }
            }
            if(isset($request->time_limit)) $data['data']['payment_times'] = $this->getPaymentTime($request->time_limit ?? 'null');
            if(isset($request->register_days)) $data['data']['register_days'] = $request->register_days;
            if(isset($request->coin_holding)) $data['data']['coin_holding'] = $request->coin_holding;

            if($request->ads_type == TRADE_BUY_TYPE) {
                $checkBlanceResponse = $this->check_balance($request->coin_type,$request->amount,true);
                if(!$checkBlanceResponse['success']) return $checkBlanceResponse;
                $data['data']['wallet_id'] = $checkBlanceResponse['data']->id;
                $data['data']['coin_id'] = $checkBlanceResponse['data']->coin_id;
                $data['data']['sold'] = 0;
            } else {
                $checkBlanceResponse = $this->check_balance($request->coin_type,$request->amount,false);
                if(!$checkBlanceResponse['success']) return $checkBlanceResponse;
                $data['data']['wallet_id'] = $checkBlanceResponse['data']->id;
                $data['data']['coin_id'] = $checkBlanceResponse['data']->coin_id;
                $data['data']['sold'] = 0;
            }

            $model = ((isset($request->ads_type) && $request->ads_type == TRADE_BUY_TYPE) ?
                     PBuy::class :((isset($request->ads_type) && $request->ads_type == TRADE_SELL_TYPE) ?
                     PSell::class : null));
            if($model == null) throw new \Exception(__('Model not found'));
            $paymentMethod = $this->repo->createOrUpdate($model,$data);
            if ($paymentMethod['success']) $paymentMethod['message'] = $successRes;
            else $paymentMethod['message'] = $errorRes;
            return $paymentMethod;
        } catch (\Exception $e) {
            storeException('createUserPaymentMethod p2p', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    private function getPaymentTime($uid)
    {
        try {
            $query = [
                ["where" => ["uid", $uid]],
                "first" => []
            ];
            $response = $this->repo->getModelData(PPaymentTime::class,$query);
            if (isset($response['success']) && $response['success'])
                return $response['data']->time ?? 0;
            return 0;
        } catch (\Exception $e) {
            storeException("getPaymentTime", $e->getMessage());
            return 0;
        }
    }

    private function check_balance($coin, $amount, $buy){
        try {
            $query = [
                ['where' => ['user_id', authUserId_p2p() ?? 0]],
                ['where' => ['coin_type', $coin]],
                ['where' => ['status', STATUS_ACTIVE]],
                'first' => [],
            ];
            $response = $this->repo->getModelData(P2PWallet::class,$query);
            if (isset($response['success']) && $response['success'])
            {
                $wallet = $response['data'];
                if($buy) return responseData(true,__("Success"),$wallet) ;
                if($wallet->balance >= $amount){
                    if(!$buy){
                        DB::beginTransaction();
                        $wallet->decrement('balance', $amount);
                        DB::commit();
                    } return $response;
                }
                else return responseData(false,__("You do not have enough balance"));
            }
            $response['message'] = __("Wallet not found");
            return $response;
        } catch (\Exception $e) {
            storeException('check Blance p2p',$e->getMessage());
            return responseData(false,__("Something went wrong"));
        }
    }

    public function adsCreateSetting()
    {
        try {
            // create user wallet if missing
            create_coin_wallet_p2p(authUserId_p2p());
            $checkValidation = $this->checkValidation();
            if(isset($checkValidation['success']) && !$checkValidation['success']) return $checkValidation;
            $data = [];
            $data['assets']   = DB::table('coins')->join('p_coin_settings', 'coins.coin_type','p_coin_settings.coin_type')
                              ->where('coins.status', STATUS_ACTIVE)->where('p_coin_settings.trade_status', STATUS_ACTIVE)
                              ->get(['name','p_coin_settings.coin_type','p_coin_settings.maximum_price','p_coin_settings.minimum_price']);
            $data['currency'] = DB::table('currency_lists')->join('p_currency_settings', 'currency_lists.code','p_currency_settings.currency_code')
                              ->where('currency_lists.status', STATUS_ACTIVE)->where('p_currency_settings.trade_status', STATUS_ACTIVE)
                              ->get(['name','p_currency_settings.currency_code','p_currency_settings.maximum_price','p_currency_settings.minimum_price']);
            $data['payment_method'] = BankRecord::getUserPaymentMethod(authUserId_p2p() ?? 0)->get();

            $data['payment_method']->map(function($item){
                if ($item->bank && isset($item->bank)) {
                    $bank = json_decode($item->bank, true);
                    if(json_last_error() !== JSON_ERROR_NONE || !is_array($bank)) $bank = [];
                        $item->bank = $bank;
                }
                return $item;
            });

            $data['is_payment_method_available'] = $data['payment_method']->count() > 0;
            $data['payment_time'] = PPaymentTime::where('status', STATUS_ACTIVE)->get(['uid','time']);
            $data['country'] = CountryList::where('status', STATUS_ACTIVE)->get(['key','value']);
            $data['counterparty'] = filter_var(settings(['counterparty_condition'])['counterparty_condition'] ?? false, FILTER_VALIDATE_BOOLEAN);
            return responseData(true,__("Ads setting get successfully"),$data);
        } catch (\Exception $e) {
            storeException('adsCreateSetting service',$e->getMessage());
            return responseData(false,__("Something went wrong"));
        }
    }

    private function manualKycCheck($settings, $verification, $user)
    {
        try {
            foreach(KYC_LIST_ARRAY as $kyc){
                if(isset($settings->$kyc) && $settings->$kyc == STATUS_ACTIVE){

                    if($kyc == 'p_phone_verification'){
                        if(!$user->phone_verified)
                        return responseData(false, __("Your phone verification is not completed"));
                        return responseData(true, __("Success"));
                    }

                    if($kyc == 'p_email_verification'){
                        if(!$user->is_verified)
                        return responseData(false, __("Your emali verification is not completed"));
                        return responseData(true, __("Success"));
                    }

                    if($kyc == 'p_passport_verification'){
                        $details = $verification->whereIn('field_name',['pass_front','pass_back','pass_selfie'])->all() ?? [];
                        if(empty($details) || count($details) < 3)
                        return responseData(false, __("Your passport verification is not completed"));
                        return responseData(true, __("Success"));
                    }

                    if($kyc == 'p_nid_verification'){
                        $details = $verification->whereIn('field_name',['nid_front','nid_back','nid_selfie'])->all() ?? [];
                        if(empty($details) || count($details) < 3)
                        return responseData(false, __("Your NID verification is not completed"));
                        return responseData(true, __("Success"));
                    }

                    if($kyc == 'p_driving_verification'){
                        $details = $verification->whereIn('field_name',['drive_front','drive_back','drive_selfie'])->all() ?? [];
                        if(empty($details) || count($details) < 3)
                        return responseData(false, __("Your driving licence verification is not completed"));
                        return responseData(true, __("Success"));
                    }

                    if($kyc == 'p_voter_verification'){
                        $details = $verification->whereIn('field_name',['voter_front','voter_back','voter_selfie'])->all() ?? [];
                        if(empty($details) || count($details) < 3)
                        return responseData(false, __("Your voter card verification is not completed"));
                        return responseData(true, __("Success"));
                    }

                }
            }
            return responseData(true, __("success"));
            // return responseData(false, __("KYC method not matched or no records found"));
        } catch (\Exception $e) {
            storeException("manualKycCheck p2p",$e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    private function personsKycCheck($user)
    {
        try {
            if($verify = ThirdPartyKycDetails::where(["user_id" => $user->id, "kyc_type" => KYC_TYPE_PERSONA])->first())
            {
                if(isset($verify->is_verified) && $verify->is_verified == STATUS_ACTIVE)
                return responseData(true, __("Success"));
                return responseData(false, __("Your driving licence verification is not completed"));
            }
            return responseData(false, __("KYC not verified or no records found"));
        } catch (\Exception $e) {
            storeException("personsKycCheck", $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    private function checkValidation()
    {
        try {
            $user = authUser_p2p();
            $settings = AdminSetting::get()->toSlugValueP2P();

            if(isset($settings->kyc_type_is) && $settings->kyc_type_is == KYC_TYPE_MANUAL){
                $verification = VerificationDetails::where('user_id',$user->id)->where('status',STATUS_ACTIVE)->get() ?? collect();
                $response = $this->manualKycCheck($settings, $verification, $user);
                if(isset($response['success']) && !$response['success'])
                return $response;
            }

            if(isset($settings->kyc_type_is) && $settings->kyc_type_is == KYC_TYPE_PERSONA &&
               isset($settings->p_persona_verification) && $settings->p_persona_verification == STATUS_ACTIVE){
                $response = $this->personsKycCheck($user);
                if(isset($response['success']) && !$response['success'])
                return $response;
            }
            return responseData(true,__("Success"));
        } catch (\Exception $e) {
            storeException('p2p checkValidation',$e->getMessage());
            return responseData(false,__("Something went wrong"));
        }
    }

    public function adsPriceGet($request)
    {
        try {
            $data = [];
            $buy  = PBuy::where(['coin_type' => $request->coin_type, 'currency' => $request->currency, 'status' => STATUS_ACTIVE])->orderBy('available', 'DESC')->first();
            $sell = PSell::where(['coin_type' => $request->coin_type, 'currency' => $request->currency, 'status' => STATUS_ACTIVE])->orderBy('available', 'ASC')->first();
            $price = convert_currency(1, $request->currency, $request->coin_type);
            $data['highest_price'] = $buy->price ?? $price;
            $data['lowest_price']  = $sell->price ?? $price;
            $data['price']  = $price;
            return responseData(true,__("Ads price get successfully"),$data);
        } catch (\Exception $e) {
            storeException('adsCreateSetting service',$e->getMessage());
            return responseData(false,__("Something went wrong"));
        }
    }

    public function adsStatusChange($request)
    {
        try {
            $table = $request->type == TRADE_BUY_TYPE ? PBuy::class : PSell::class;
            $data = $table::where(['uid' => $request->id, 'user_id' => authUserId_p2p()])->first();
            if($data){
                $data->status = !$data->status;
                $data->update();
                $data = ['id' => $data->uid, 'status' => $data->status];
                return responseData(true,__("Ads Status Changed"),$data);
            }
            return responseData(false,__("Ads not found"));
        } catch (\Exception $e) {
            storeException('adsStatusChange service',$e->getMessage());
            return responseData(false,__("Something went wrong"));
        }
    }

    public function adsFilterChange($request)
    {
        try {
            $table = $request->type == TRADE_BUY_TYPE ? PSell::class : PBuy::class;
            $data = $table::whereCoinType($request->coin)->whereStatus(STATUS_ACTIVE); //->whereCurrency($request->currency)
            if($request->amount > 0){
                $data = $data->where('minimum_trade_size', "<=", $request->amount);
            }

            if(isset($request->currency)){
                if($request->currency == "all"){
                }else{
                    $data = $data->whereCurrency($request->currency);
                }

            }

            if(isset($request->payment_method)){
                if($request->payment_method == "all"){
                }else{
                    $paymentMethods = explode(',',$request->payment_method);
                    if(isset($paymentMethods[0])){
                        $data = $data->where(function($q) use($paymentMethods){
                            $count = 0;
                            $search = $q->where('admin_payment_method', 'LIKE', "%{$paymentMethods[0]}%");
                            if(isset($paymentMethods[1])){
                                foreach($paymentMethods as $payment){
                                    if($count == 0) $count++;
                                    else $search= $search->orWhere('admin_payment_method', 'LIKE', "%{$payment}%");
                                }
                            }
                            return $search;
                        });
                    }
                }

            }

            if(isset($request->country)){
                if($request->country == "all"){
                }else{
                    $Country = explode(',',$request->country);
                    if(isset($Country[0])){
                        $data = $data->where(function($q) use($Country){
                            $count = 0;
                            $search = $q->orWhere('country', 'LIKE', "%{$Country[0]}%");
                            if(isset($Country[1])){
                                foreach($Country as $country){
                                    if($count == 0) $count++;
                                    else $search= $search->orWhere('country', 'LIKE', "%{$country}%");
                                }
                            }
                            return $search;
                        });
                    }
                }

            }
            if($data->exists()){
                $data = $data->get();
                $data->map(function($query){
                    if(isset($query->payment_method)){
                        $paymentMethods = [];
                        $payment = explode(',',$query->payment_method);
                        foreach($payment as $p){
                            $paymentMethod = BankRecord::where('status', STATUS_ACTIVE)
                                ->where("id", $p)
                                ->where("access", "LIKE", "%" . BankFormAccessType::P2P->value . "%")
                                ->with(['bank_form.paymentMethod'])->first();
                            if($paymentMethod){
                                if ($paymentMethod->bank && isset($paymentMethod->bank)) {
                                    $bank = json_decode($paymentMethod->bank, true);
                                    if(json_last_error() !== JSON_ERROR_NONE || !is_array($bank)) $bank = [];
                                    $paymentMethod->bank = $bank;
                                }
                                $paymentMethods[] = (object)$paymentMethod;
                            }
                        }
                        $query->payment_method_list = $paymentMethods;
                    }
                    if($query->user = $query->user()->first() ?? false && isset($query->user)){
                        $query->user->photo = imageSrcUser($query->user->photo ?? '',IMG_USER_VIEW_PATH);
                    }
                    $price = $query->price;
                    if($query->price_type == TRADE_PRICE_FLOAT_TYPE){
                        $rate = 100;
                        $floatPrice = 0;
                        if($query->price_rate < 100){
                            $floatPrice = convert_currency(1, $query->coin_type, $query->currency);
                            $rate = 100 - $query->price_rate;
                            $result = ($floatPrice - (($floatPrice * $rate)/100));
                            $price = $result;
                        }
                        if($query->price_rate > 100) {
                            $floatPrice = convert_currency(1, $query->coin_type, $query->currency);
                            $rate = $query->price_rate - 100;
                            $result = ($floatPrice + (($floatPrice * $rate)/100));
                            $price = $result;
                        }
                    }
                    $query->price = $price;
                });
                if($table == PSell::class)
                    $data = $data->sortByDesc('price');
                else
                    $data = $data->sortBy('price');
                $data = $data->paginate($request->per_page ?? 10);
                return responseData(true,__("Ads get successfully"),$data);
            }
            return responseData(false,__("No Ads Found"),$data);
        } catch (\Exception $e) {
            storeException('adsFilterChange service',$e->getMessage());
            return responseData(false,__("Something went wrong"));
        }
    }

    public function userAdsFilterChange($request)
    {
        try {
            $buy = collect([]);
            $sell = collect([]);
            $tables = [PBuy::class , PSell::class];
            foreach($tables as $table) {
                // ignore loop if requested type is buy and table is sell
                if(($request->type == TRADE_BUY_TYPE) && ($table == PSell::class))
                continue;

                //// ignore loop if requested type is sell and table is buy
                if(($request->type == TRADE_SELL_TYPE) && ($table == PBuy::class))
                continue;

                // get buy or sell data
                $data = $table::with('user')->where("user_id", authUserId_p2p());

                // check if request not want all coins
                if($request->coin != 'all')
                    $data = $data->whereIn("coin_type",explode(',',$request->coin));

                // check if request not want all status
                if($request->ads_status != 'all')
                    $data = $data->where("status",$request->ads_status);

                // get data from a date to another data
                if(isset($request->from_date) && isset($request->to_date))
                    $data = $data->whereBetween('created_at', [date('Y-m-d',strtotime($request->from_date)), date('Y-m-d',strtotime($request->to_date))]);

                $data = $data->orderBy('created_at','DESC')->paginate($request->per_page ?? 5);
            }

            if(!empty($data)){
                $data->map(function($ads){
                    if(filled($ads->user)){
                        $ads->user->photo = filled($ads->user->photo) 
                            ? imageSrcUser($ads->user->photo ?? '',IMG_USER_VIEW_PATH)
                            : null;
                    }
                });
                return responseData(true,__("Ads get successfully"),$data);
            }
            return responseData(false,__("No Ads"),$data);
        } catch (\Exception $e) {
            storeException("userAdsFilterChange", $e->getMessage());
            return responseData(false,__("Something went wrong"));
        }
    }

    public function availableBalance($request)
    {
        try {
            if(isset($request->uid)){
                $ads = $request->model::where('uid',$request->uid)->first();
                if($ads){
                    $data = [
                        'balance' => $ads->available ?? 0
                    ];
                    return responseData(true,__("Balance found successfully"),$data);
                }
                return responseData(false,__("Ads not found"));
            }else{
                create_coin_wallet_p2p(authUserId_p2p());
                $wallet = $request->model::where(['user_id'=> authUserId_p2p(), 'coin_type'=>$request->coin_type])->first();
                if($wallet){
                    $data = [
                        'balance' => $wallet->balance ?? 0
                    ];
                    return responseData(true,__("Balance found successfully"),$data);
                }
                return responseData(false,__("Wallet not found"));
            }
        } catch (\Exception $e) {
            storeException('availableBalance service',$e->getMessage());
            return responseData(false,__("Something went wrong"));
        }
    }


    public function getMarketPrice($request)
    {
        try {
            $price = convert_currency(1, $request->currency, $request->coin);
            return responseData(true, __("Market price get successfully"), ["price" => $price]);
        } catch (\Exception $e) {
            storeException("getMarketPrice", $e->getMessage());
            return responseData(false,__("Something went wrong"));
        }
    }

    public function adsDetails($request)
    {
        try {
            $data = [];
            $table = $request->ads_type == TRADE_BUY_TYPE ? PSell::class : PBuy::class ;
            $ads = $table::where("uid", $request->uid)->with('user')->first();
            if($ads){
                if(isset($ads->payment_method)){
                    $paymentMethods = [];
                    $payment = explode(',',$ads->payment_method);
                    foreach($payment as $p){
                        $paymentMethod = BankRecord::where("id", $p)
                            ->where("access", "LIKE", "%" . BankFormAccessType::P2P->value . "%")
                            ->with(['bank_form.paymentMethod'])->first();
                        if($paymentMethod){
                            if ($paymentMethod->bank && isset($paymentMethod->bank)) {
                                $bank = json_decode($paymentMethod->bank, true);
                                if(json_last_error() !== JSON_ERROR_NONE || !is_array($bank)) $bank = [];
                                    $paymentMethod->bank = $bank;
                            }
                            $paymentMethods[] = (object)$paymentMethod;
                        }
                    }
                    $data['payment_methods'] = $paymentMethods;
                }
                $user = User::find($ads->user_id);
                $orders = POrder::where(fn($q)=>$q->where('seller_id', $user->id)->orWhere('buyer_id', $user->id));
                $helperResponse = userTradeInfo($user,$orders);
                $data['ads']               = $ads;
                $data['orders']            = $helperResponse['total_trade'] ?? 0;
                $data['completion']        = $helperResponse['completion_rate_30d'] ?? 0;
                $data['price']             = $ads->price ?? 0;
                $data['available']         = $ads->available ?? 0;
                $data['payment_time']      = $ads->payment_time ?? 0;
                $data['termsAndCondition'] = $ads->terms ?? '';
                // $data['payment_methods']   = $this->getPaymentMethods($ads->payment_method);
                $data['minimum_price']     = $ads->minimum_trade_size ?? '';
                $data['maximaum_price']    = $ads->maximum_trade_size ?? '';
                return responseData(true, __("Ads Details get successfully"), $data);
            }
            return responseData(true, __("Ads Details not found"));
        } catch (\Exception $e) {
            storeException("adsDetails", $e->getMessage());
            return responseData(false,__("Something went wrong"));
        }
    }

    private function getPaymentMethods($paymentMethod)
    {
        try {
            $data = collect();
            $paymentMethod = explode(",",$paymentMethod);
            foreach ($paymentMethod as $payment){
                $p = BankRecord::where(['user_id' =>authUserId_p2p() ?? 0, 'status' => STATUS_ACTIVE])
                    ->where("id", $payment)
                    ->where("access", "LIKE", "%" . BankFormAccessType::P2P->value . "%")
                    ->with(['bank_form.paymentMethod'])->first();
                if($p)$data->add($p?->bank_form?->paymentMethod?->name ?? "N/A");
            }
            return $data;
        } catch (\Exception $e) {
            storeException("getPaymentMethods", $e->getMessage());
            return responseData(false,__("Something went wrong"));
        }
    }

    public function adsDelete($request)
    {
        try {
            if($request->ads_type == TRADE_BUY_TYPE){
                if($ads = PBuy::where(['uid' => $request->uid, 'user_id' => authUserId_p2p()])->first()){
                    $ads->delete();
                    return responseData(true,__("Ads Deleted successfully!"));
                }
                return responseData(false,__("Ads not found!"));
            }
            if($request->ads_type == TRADE_SELL_TYPE){
                if($ads = PSell::where(['uid' => $request->uid, 'user_id' => authUserId_p2p()])->first()){
                    if($ads->available > 0){
                        if($wallet = P2PWallet::find($ads->wallet_id)){
                             $wallet->increment("balance", $ads->available);
                             $ads->delete();
                             return responseData(true,__("Ads Deleted successfully!"));
                        } return responseData(false,__("Wallet not found!"));
                    }
                    $ads->delete();
                    return responseData(true,__("Ads Deleted successfully!"));
                }
                return responseData(false,__("Ads not found!"));
            }
            return responseData(false,__("Ads type not matched "));
        } catch (\Exception $e) {
            storeException("adsDelete", $e->getMessage());
            return responseData(false,__("Something went wrong"));
        }
    }

    public function adsMarketSetting()
    {
        try {
            $data = $this->landingP2pData();
            $data['assets']   = DB::table('coins')->join('p_coin_settings', 'coins.coin_type','p_coin_settings.coin_type')
                              ->where('coins.status', STATUS_ACTIVE)->where('p_coin_settings.trade_status', STATUS_ACTIVE)
                              ->get(['name','p_coin_settings.coin_type','p_coin_settings.maximum_price','p_coin_settings.minimum_price']);
            $data['currency'] = DB::table('currency_lists')->join('p_currency_settings', 'currency_lists.code','p_currency_settings.currency_code')
                              ->where('currency_lists.status', STATUS_ACTIVE)->where('p_currency_settings.trade_status', STATUS_ACTIVE)
                              ->get(['name','p_currency_settings.currency_code','p_currency_settings.maximum_price','p_currency_settings.minimum_price']);
            $data['payment_method'] = PPaymentMethod::with(['bank_form.fields'])->where('status', STATUS_ACTIVE)->get();
            $data['payment_method_landing'] = PPaymentMethod::with(['bank_form.fields'])->where('status', STATUS_ACTIVE)->limit(6)->get();
            $data['country'] = CountryList::where('status', STATUS_ACTIVE)->get(['key','value']);
            $data['p2p_faq'] = Faq::where(['status' => STATUS_ACTIVE, 'faq_type_id' => FAQ_TYPE_P2P])->get();
            $data['total_trade'] = 11;
            $data['completion'] = '100%';

            $data['payment_method']->map(function($p){
                $p->logo = isset($p->logo)? asset(PAYMENT_METHOD_LOGO_PATH.($p->logo)): null;
            });
            $data['payment_method_landing']->map(function($p){
                $p->logo = isset($p->logo)? asset(PAYMENT_METHOD_LOGO_PATH.($p->logo)): null;
            });

            return responseData(true,__("Ads market data get successfully"), $data);
        } catch (\Exception $e) {
            storeException("adsMarketSetting", $e->getMessage());
            return responseData(false,__("Something went wrong"));
        }
    }

    public function landingP2pData()
    {
        $data['p2p_banner_img'] = settings('p2p_banner_img') ? p2pLandingImg('p2p_banner_img') : "https://images.unsplash.com/photo-1631758236057-0aedf1bc584d?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1172&q=80";
        $data['p2p_banner_header'] = settings('p2p_banner_header') ? settings('p2p_banner_header') : "Tradexpro Peer-to-Peer Ecosystem With unlimited countries and payment system";
        $data['p2p_banner_des'] = settings('p2p_banner_des') ? settings('p2p_banner_des') : "Tradexpro is the largest centralized exchange globally. However, it is also a major player in the P2P trading space";

        $data['p2p_buy_step_1_heading'] = settings('p2p_buy_step_1_heading') ? settings('p2p_buy_step_1_heading') : "Place Buy Order";
        $data['p2p_buy_step_1_des'] = settings('p2p_buy_step_1_des') ? settings('p2p_buy_step_1_des') : "Lorem ipsum dolor sit amet, consectetur adipisicing elit. Facilis etveniam est earum consequuntur? Ut nulla sequi repudiandae. Molestiae nobis";
        $data['p2p_buy_step_1_icon'] = settings('p2p_buy_step_1_icon') ? p2pLandingImg('p2p_buy_step_1_icon') : "https://img.icons8.com/fluency/1x/file.png";

        $data['p2p_buy_step_2_heading'] = settings('p2p_buy_step_2_heading') ? settings('p2p_buy_step_2_heading') : "Make Payment";
        $data['p2p_buy_step_2_des'] = settings('p2p_buy_step_2_des') ? settings('p2p_buy_step_2_des')  : "Lorem ipsum dolor sit amet, consectetur adipisicing elit. Facilis etveniam est earum consequuntur? Ut nulla sequi repudiandae. Molestiae nobis";
        $data['p2p_buy_step_2_icon'] = settings('p2p_buy_step_2_icon') ? p2pLandingImg('p2p_buy_step_2_icon') : "https://img.icons8.com/doodle/256/bank.png";

        $data['p2p_buy_step_3_heading'] = settings('p2p_buy_step_3_heading') ? settings('p2p_buy_step_3_heading') : "Release Crypto";
        $data['p2p_buy_step_3_des'] = settings('p2p_buy_step_3_des') ? settings('p2p_buy_step_3_des') : "Lorem ipsum dolor sit amet, consectetur adipisicing elit. Facilis etveniam est earum consequuntur? Ut nulla sequi repudiandae. Molestiae nobis";
        $data['p2p_buy_step_3_icon'] = settings('p2p_buy_step_3_icon') ? p2pLandingImg('p2p_buy_step_3_icon') : "https://img.icons8.com/doodle/256/coins.png";

        $data['p2p_sell_step_1_heading'] = settings('p2p_sell_step_1_heading') ? settings('p2p_sell_step_1_heading') : "Place Sell Order";
        $data['p2p_sell_step_1_des'] = settings('p2p_sell_step_1_des') ? settings('p2p_sell_step_1_des') : "Lorem ipsum dolor sit amet, consectetur adipisicing elit. Facilis etveniam est earum consequuntur? Ut nulla sequi repudiandae. Molestiae nobis";
        $data['p2p_sell_step_1_icon'] = settings('p2p_sell_step_1_icon') ? p2pLandingImg('p2p_sell_step_1_icon') : "https://img.icons8.com/doodle/256/file--v1.png";

        $data['p2p_sell_step_2_heading'] = settings('p2p_sell_step_2_heading') ? settings('p2p_sell_step_2_heading') : "Waiting for Payment";
        $data['p2p_sell_step_2_des'] = settings('p2p_sell_step_2_des') ? settings('p2p_sell_step_2_des') : "Lorem ipsum dolor sit amet, consectetur adipisicing elit. Facilis etveniam est earum consequuntur? Ut nulla sequi repudiandae. Molestiae nobis";
        $data['p2p_sell_step_2_icon'] = settings('p2p_sell_step_2_icon') ? p2pLandingImg('p2p_sell_step_2_icon') : "https://img.icons8.com/doodle/256/bribery.png";

        $data['p2p_sell_step_3_heading'] = settings('p2p_sell_step_3_heading') ? settings('p2p_sell_step_3_heading') : "Release Crypto";
        $data['p2p_sell_step_3_des'] = settings('p2p_sell_step_3_des') ? settings('p2p_sell_step_3_des') : "Lorem ipsum dolor sit amet, consectetur adipisicing elit. Facilis etveniam est earum consequuntur? Ut nulla sequi repudiandae. Molestiae nobis";
        $data['p2p_sell_step_3_icon'] = settings('p2p_sell_step_3_icon') ? p2pLandingImg('p2p_sell_step_3_icon') : "https://img.icons8.com/doodle/256/stack-of-coins.png";

        $data['p2p_advantage_right_image'] = settings('p2p_advantage_right_image') ? p2pLandingImg('p2p_advantage_right_image') : "https://img.icons8.com/fluency/1x/file.png";

        $data['p2p_advantage_1_heading'] = settings('p2p_advantage_1_heading') ? settings('p2p_advantage_1_heading') : "Low Transaction Fees";
        $data['p2p_advantage_1_des'] = settings('p2p_advantage_1_des') ? settings('p2p_advantage_1_des') : "Lorem ipsum dolor sit amet, consectetur adipisicing elit. Facilis etveniam est earum consequuntur? Ut nulla sequi repudiandae. Molestiae nobis";
        $data['p2p_advantage_1_icon'] = settings('p2p_advantage_1_icon') ? p2pLandingImg('p2p_advantage_1_icon') : "https://img.icons8.com/doodle/256/tax.png";

        $data['p2p_advantage_2_heading'] = settings('p2p_advantage_2_heading') ? settings('p2p_advantage_2_heading') : "Flexible Payment Methods";
        $data['p2p_advantage_2_des'] = settings('p2p_advantage_2_des') ? settings('p2p_advantage_2_des') : "Lorem ipsum dolor sit amet, consectetur adipisicing elit. Facilis etveniam est earum consequuntur? Ut nulla sequi repudiandae. Molestiae nobis";
        $data['p2p_advantage_2_icon'] = settings('p2p_advantage_2_icon') ? p2pLandingImg('p2p_advantage_2_icon') : "https://img.icons8.com/doodle/256/check.png";

        $data['p2p_advantage_3_heading'] = settings('p2p_advantage_3_heading') ? settings('p2p_advantage_3_heading') : "Trade at Your Preferred Prices";
        $data['p2p_advantage_3_des'] = settings('p2p_advantage_3_des') ? settings('p2p_advantage_3_des') : "Lorem ipsum dolor sit amet, consectetur adipisicing elit. Facilis etveniam est earum consequuntur? Ut nulla sequi repudiandae. Molestiae nobis";
        $data['p2p_advantage_3_icon'] = settings('p2p_advantage_3_icon') ? p2pLandingImg('p2p_advantage_3_icon') : "https://img.icons8.com/doodle/256/money.png";

        $data['p2p_advantage_4_heading'] = settings('p2p_advantage_4_heading') ? settings('p2p_advantage_4_heading') : "Protecting Your Privacy";
        $data['p2p_advantage_4_des'] = settings('p2p_advantage_4_des') ? settings('p2p_advantage_4_des') : "Lorem ipsum dolor sit amet, consectetur adipisicing elit. Facilis etveniam est earum consequuntur? Ut nulla sequi repudiandae. Molestiae nobis";
        $data['p2p_advantage_4_icon'] = settings('p2p_advantage_4_icon') ? p2pLandingImg('p2p_advantage_4_icon') : "https://img.icons8.com/doodle/256/security-configuration.png";

        return $data;
    }

    public function adsEdit($request)
    {
        try {
            DB::beginTransaction();
            $data = [
                'price_type' => $request->price_type,
                'price' => $request->price,
                'price_rate' => $request->price_rate,
                'available' => $request->amount,
                'minimum_trade_size' => $request->min_limit,
                'maximum_trade_size' => $request->max_limit,
                'terms' => $request->terms,
                'auto_reply' => $request->auto_reply,
                'country' => $request->countrys,
            ];
            if(isset($request->payment_methods)){
                $data['payment_method'] = $request->payment_methods;
                $userPaymentMethods = explode( "," ,$request->payment_methods);
                if(isset($userPaymentMethods[0])){
                    $payments = '';
                    foreach($userPaymentMethods as $payment){
                        $adminPayment = BankRecord::where('id',$payment)
                            ->where("access", "LIKE", "%" . BankFormAccessType::P2P->value . "%")
                            ->where('user_id', authUserId_p2p() ?? '')
                            ->first();
                        if($adminPayment){
                            if($payments != '') $payments .= ",";
                            $payments .= $adminPayment->payment_uid;
                        }else{
                            return responseData(false, __("Payment method not exist !!"));
                        }
                    }
                    $data['admin_payment_method'] = $payments;
                }
            }
            if(isset($request->ads_type) && $request->ads_type == TRADE_BUY_TYPE)
            {
                if($ads = PBuy::where(['uid' => $request->ads_uid, 'user_id' => authUser_p2p()->id ])->first()){
                    $ads->update($data);
                    DB::commit();
                    return responseData(true,__("Ads updated successfully"));
                }
                return responseData(false,__("Ads not found"));
            }
            if(isset($request->ads_type) && $request->ads_type == TRADE_SELL_TYPE)
            {
                if($ads = PSell::where(['uid' => $request->ads_uid, 'user_id' => authUser_p2p()->id ])->first()){
                    if($ads->available > $request->amount){
                        $returnAmount = ($ads->available - $request->amount);
                        $wallet = P2PWallet::find($ads->wallet_id);
                        $wallet->increment("balance", $returnAmount);
                    }
                    if($ads->available < $request->amount){
                        $removeAmount = ($request->amount - $ads->available);
                        $wallet = P2PWallet::find($ads->wallet_id);
                        $wallet->decrement("balance", $removeAmount);
                    }
                    $ads->update($data);
                    DB::commit();
                    return responseData(true,__("Ads updated successfully"));
                }
                return responseData(false,__("Ads not found"));
            }
            return responseData(false,__("Ads type not matched"));
        } catch (\Exception $e) {
            DB::rollBack();
            storeException("adsEdit", $e->getMessage());
            return responseData(false,__("Something went wrong"));
        }
    }
    public function myAdsDetails($request)
    {
        try {
            $data = [];
            $table = $request->ads_type == TRADE_BUY_TYPE ? PBuy::class : PSell::class ;
            $ads = $table::where(["uid" => $request->uid, 'user_id' => authUserId_p2p()])->with('user')->first();
            if($ads){
                $ads->payment_method = BankRecord::where(['user_id' =>authUserId_p2p() ?? 0, 'status' => STATUS_ACTIVE])
                    ->whereIn('id', explode(',',$ads->payment_method ?? ""))
                    ->where("access", "LIKE", "%" . BankFormAccessType::P2P->value . "%")
                    ->with(['bank_form.paymentMethod'])->get();

                $ads->payment_method->map(function($item){
                    if ($item->bank && isset($item->bank)) {
                        $bank = json_decode($item->bank, true);
                        if(json_last_error() !== JSON_ERROR_NONE || !is_array($bank)) $bank = [];
                            $item->bank = $bank;
                    }
                    return $item;
                });

                if($a = PPaymentTime::where('uid', $ads->payment_times)->first()){
                    $ads->payment_times = [
                        'uid' => $a->uid,
                        'value' => $a->time,
                    ];
                }else{
                    $ads->payment_times = [
                        'uid' => '',
                        'value' => '',
                    ];
                }
                $ads->country = CountryList::whereIn('key', explode(',',$ads->country))->get();
                $data = [];
                $data['ads'] = $ads;
                $data['assets']   = DB::table('coins')->join('p_coin_settings', 'coins.coin_type','p_coin_settings.coin_type')
                                  ->where('coins.status', STATUS_ACTIVE)->where('p_coin_settings.trade_status', STATUS_ACTIVE)
                                  ->get(['name','p_coin_settings.coin_type','p_coin_settings.maximum_price','p_coin_settings.minimum_price']);
                $data['currency'] = DB::table('currency_lists')->join('p_currency_settings', 'currency_lists.code','p_currency_settings.currency_code')
                                  ->where('currency_lists.status', STATUS_ACTIVE)->where('p_currency_settings.trade_status', STATUS_ACTIVE)
                                  ->get(['name','p_currency_settings.currency_code','p_currency_settings.maximum_price','p_currency_settings.minimum_price']);
                $data['payment_method'] = BankRecord::where(['user_id' =>authUserId_p2p() ?? 0, 'status' => STATUS_ACTIVE])
                    ->where("access", "LIKE", "%" . BankFormAccessType::P2P->value . "%")
                    ->with(['bank_form.paymentMethod'])->get();

                $data['payment_method']->map(function($item){
                    if ($item->bank && isset($item->bank)) {
                        $bank = json_decode($item->bank, true);
                        if(json_last_error() !== JSON_ERROR_NONE || !is_array($bank)) $bank = [];
                            $item->bank = $bank;
                    }
                    return $item;
                });
                $data['is_payment_method_available'] = $data['payment_method']->count() > 0;
                $data['payment_time'] = PPaymentTime::where('status', STATUS_ACTIVE)->get(['uid','time']);
                $data['country'] = CountryList::where('status', STATUS_ACTIVE)->get(['key','value']);
                $data['counterparty'] = filter_var(settings(['counterparty_condition'])['counterparty_condition'] ?? false, FILTER_VALIDATE_BOOLEAN);

                return responseData(true, __("My ads Details get successfully"), $data);
            }
            return responseData(true, __("Ads Details not found"));
        } catch (\Exception $e) {
            DB::rollBack();
            storeException("adsEdit", $e->getMessage());
            return responseData(false,__("Something went wrong"));
        }
    }

    public function getCountry($country)
    {
        if($country !== 'all') return $country;
        $countrys = CountryList::whereStatus(STATUS_ACTIVE)->get('key');
        $country_text = '';
        foreach ($countrys as $country) {
            if($country_text == '') $country_text .= $country->key;
            else $country_text .= ','.$country->key;
        }
        return $country_text;
    }

}
