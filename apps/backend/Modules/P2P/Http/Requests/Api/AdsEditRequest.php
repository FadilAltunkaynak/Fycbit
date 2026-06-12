<?php
namespace Modules\P2P\Http\Requests\Api;

use App\Model\CountryList;
use App\Model\CurrencyList;
use App\Model\DynamicBank\BankRecord;
use Modules\P2P\Entities\PBuy;
use Modules\P2P\Rules\EqualTo;
use Modules\P2P\Entities\PSell;
use Illuminate\Http\JsonResponse;
use Modules\P2P\Rules\CallBackRule;
use Modules\P2P\Rules\CheckMultiData;
use Modules\P2P\Entities\PCoinSetting;
use Modules\P2P\Entities\PPaymentMethod;
use Modules\P2P\Entities\PCurrencySetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class AdsEditRequest extends FormRequest
{
    private $coinMax;
    private $coinMin;
    private $currencyMax;
    private $currencyMin;
    public function __construct()
    {
        $currencySetting = PCurrencySetting::where("currency_code", $_POST["fiat_type"] ?? '')->first();
        $this->currencyMin = $currencySetting->minimum_price ?? 0;
        $this->currencyMax = $currencySetting->maximum_price ?? 0;
        $coinSetting = PCoinSetting::where("coin_type", $_POST["coin_type"] ?? '')->first();
        $this->coinMin = $coinSetting->minimum_price ?? 0;
        $this->coinMax = $coinSetting->maximum_price ?? 0;
    }
        /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $Buy = TRADE_BUY_TYPE;
        $sell = TRADE_SELL_TYPE;
        $Fixed = TRADE_PRICE_FIXED_TYPE;
        $Float = TRADE_PRICE_FLOAT_TYPE;
        
        $coinMin = $this->coinMin;
        $coinMax = $this->coinMax;
        
        $currencyMin = $this->currencyMin;
        $currencyMax = $this->currencyMax;

        $table = isset($this->ads_type) && $this->ads_type == $Buy ? 'p_buys' : 'p_sells';
        $tableClass = isset($this->type) && $this->type == $Buy ? PBuy::class : PSell::class;
        return [
            'ads_type' => "required|in:$Buy,$sell",
            'ads_uid' => "required|exists:$table,uid",
            'coin_type' => ["required","exists:coins", new CallBackRule($this, "checkCoinType", $this->messages()['coin_type.callback'])],
            'fiat_type' => ["required","exists:currency_lists,code", new CallBackRule($this, "checkCurrencyType", $this->messages()['fiat_type.callback'])], 
            'price_type' => "required|in:$Fixed,$Float",
            'price' => "required_if:price_type,$Fixed|numeric|gt:0",
            'price_rate' => "required_if:price_type,$Float|numeric",
            'amount' => "required|numeric|gte:$coinMin|lte:$coinMax",
            'min_limit' => "required|numeric|gte:$currencyMin",
            'max_limit' => "required|numeric|lte:$currencyMax",
            'payment_methods' => ["required",new CheckMultiData(BankRecord::class,"id", $this->messages()['payment_methods.checkMulti'])],
            'time_limit' => "exists:p_payment_times,uid", 
            // 'register_days' => "required|numeric",
            // 'coin_holding' => "required|numeric",
            'countrys' => ["required",new CheckMultiData(CountryList::class,"key", $this->messages()['countrys.checkMulti'])],
        ];
    }

    public function messages()
    {
        $coinMin = $this->coinMin;
        $coinMax = $this->coinMax;
        $currencyMin = $this->currencyMin;
        $currencyMax = $this->currencyMax;
        return [
            'ads_type.required' => __("Ads Type is required"),
            'ads_type.in' => __("Ads Type is invalid"),

            'ads_uid.required' => __("Ads id is required"),
            'ads_uid.exists' => __("Ads is invalid"),
            
            'coin_type.required' => __("Asset is required"),
            'coin_type.exists' => __("Asset is invalid"),
            'coin_type.callback' => __("Asset not changeable"),

            'fiat_type.required' => __("Fiat is required"),
            'fiat_type.exists' => __("Fiat is invalid"),
            'fiat_type.callback' => __("Fiat not changeable"),

            'price_type.required' => __("Price type is required"),
            'price_type.in' => __("Price type is invalid"),

            'price.required_if' => __("Price field can not be empty"),
            'price.numeric' => __("Price field is invalid"),
            'price.gt' => __("Price can not be less than 0"),

            'price_rate.required_if' => __("Price rate can not be empty"),
            'price_rate.numeric' => __("Price rate is invalid"),

            'amount.required' => __("Amount field can not be empty"),
            'amount.numeric' => __("Amount field is invalid"),
            'amount.gte' => __("Amount can not be less than :amount",["amount" => $coinMin]),
            'amount.lte' => __("Amount can not be greater than :amount",["amount" => $coinMax]),

            'min_limit.required' => __("Order minimum limit is required"),
            'min_limit.numeric' => __("Order minimum limit field is invalid"),
            'min_limit.gte' => __("Order minimum limit can not be less than :min_limit",["min_limit" => $currencyMin]),

            'max_limit.required' => __("Order maximum limit is required"),
            'max_limit.numeric' => __("Order maximum limit field is invalid"),
            'max_limit.lte' => __("Order maximum limit can not be greater than :max_limit",["max_limit" => $currencyMax]),

            'payment_methods.required' => __("Payment method can not be empty"),
            'payment_methods.checkMulti' => __("Payment method is invalid"),

            'time_limit.required' => __("Payment time is required"),
            'time_limit.exists' => __("Payment time is invalid"),

            'register_days.required' => __("Register Days can not be empty"),
            'register_days.numeric' => __("Register Days is invalid"),

            'coin_holding.required' => __("BTC coin holding can not be empty"),
            'coin_holding.numeric' => __("Register Days is invalid"),

            'countrys.required' => __("Country fields is required"),
            'countrys.checkMulti' => __("Country is invalid"),


            'coin_type.EqualTo' => __('This coin is not active for trade'),
            'fiat_type.EqualTo' => __('This currency is not active for trade'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = [];
        if ($validator->fails()) {
            $e = $validator->errors()->all();
            foreach ($e as $error) {
                $errors[] = $error;
            }
        }
        $json = [
            'success'=>false,
            'message' => $errors[0],
        ];
        $response = new JsonResponse($json, 200);
        throw (new ValidationException($validator, $response))->errorBag($this->errorBag)->redirectTo($this->getRedirectUrl());
    }

    public function checkCoinType()
    {
        $tableClass = isset($this->ads_type) && $this->ads_type == TRADE_BUY_TYPE ? PBuy::class : PSell::class;
        if($ads = $tableClass::where(['uid' => $this->ads_uid, 'coin_type' => $this->coin_type, 'user_id' => authUserId_p2p()])->first()){
            return true;
        } return false;

    }

    public function checkCurrencyType()
    {
        $tableClass = isset($this->ads_type) && $this->ads_type == TRADE_BUY_TYPE ? PBuy::class : PSell::class;
        if($ads = $tableClass::where(['uid' => $this->ads_uid, 'currency' => $this->fiat_type, 'user_id' => authUserId_p2p()])->first()){
            return true;
        } return false;
    }

}
