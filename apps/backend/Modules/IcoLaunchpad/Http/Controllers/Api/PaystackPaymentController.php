<?php

namespace Modules\IcoLaunchpad\Http\Controllers\Api;

use Modules\IcoLaunchpad\Http\Requests\PaystackPaymentRequest;
use App\Model\CurrencyList;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\IcoLaunchpad\Entities\IcoPhaseInfo;
use Modules\IcoLaunchpad\Entities\TokenBuyHistory;
use Modules\IcoLaunchpad\Http\Services\IcoTokenBuyService;

class PaystackPaymentController extends Controller
{
  private $admin_approved;
  private $tokenBuyService;
  public function __construct()
  {
    $this->tokenBuyService = new IcoTokenBuyService;
    $approved = settings('icoTokenBuy_admin_approved');
    $this->admin_approved = ($approved == STATUS_ACTIVE);
  }
    public function getPaystackPaymentURL(PaystackPaymentRequest $request)
    {
        $url = "https://api.paystack.co/transaction/initialize";

        $secret_key = allsetting('PAYSTACK_SECRET');

        $callback_url = allsetting('exchange_url').'/verify-paystack?phase_id='.$request->phase_id.'&token_id='.$request->token_id.'&amount='.$request->amount.'&payment_method='.$request->payment_method.'&api_type=ico';

        $currency_ZAR = CurrencyList::where('code','ZAR')->first();
        $currency_rate_ZAR = isset($currency_ZAR)? $currency_ZAR->rate:1;
        $converted_amount = $request->amount * $currency_rate_ZAR;

        $fields = [
          'email' => $request->email,
          'amount' => str_replace('.', '', number_format($converted_amount, 2, '.', '')),
          'callback_url'=>$callback_url
        ];
      
        $fields_string = http_build_query($fields);
        
        
        //open connection
        $ch = curl_init();
        
        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Authorization: Bearer ".$secret_key,
          "Cache-Control: no-cache",
        ));
        
        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
        
        //execute post
        $result = curl_exec($ch);

        $result_json_data = json_decode($result);
        $data = [];
        if($result_json_data->status)
        {
            $data['authorization_url'] = $result_json_data->data->authorization_url;
            $data['reference'] = $result_json_data->data->reference;
            $response = ['success'=>true, 'message'=>__('Authorization URL created'), 'data'=>$data];
        }else{
            $response = ['success'=>false, 'message'=>__('Authorization URL created is failed')];
        }
        
        return response()->json($response);
    }

  public function verificationPaystackPayment(Request $request)
  {
      if(isset($request->reference))
      {
        if(!isset($request->buy_history_id))
        {
          $response = ['success'=>false, 'message'=>__('Token Buy history id field is required!')];
          return response()->json($response);
        }
        if(!isset($request->walletAddress))
        {
          $response = ['success'=>false, 'message'=>__('WalletAddress field is required!')];
          return response()->json($response);
        }
          $secret_key = allsetting('PAYSTACK_SECRET');
          $curl = curl_init();

          curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/".$request->reference,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
              "Authorization: Bearer ".$secret_key,
              "Cache-Control: no-cache",
            ),
          ));
          
          $result = curl_exec($curl);
          $err = curl_error($curl);
        
          curl_close($curl);

          $result_json_data = json_decode($result);

          if($result_json_data->status)
          {
            $buy_history = TokenBuyHistory::find($request->buy_history_id);
            if(isset($buy_history))
            {
              $buy_history->trx_id = $request->transaction_id;
              $buy_history->save();
              $phaseInfo = IcoPhaseInfo::findOrFail($buy_history->phase_id);
              $phaseInfo->decrement('available_token_supply', $buy_history->amount);

              if ($this->admin_approved) {
                  $this->tokenBuyService->addBalance($buy_history->wallet_id, $request->walletAddress, $buy_history);
              }
            }
            // $response = ['success'=>true, 'message'=>$result_json_data->message];
            $response = ['success'=>true, 'message'=>__('Request is submitted successfully')];
          }else{
              $response = ['success'=>false, 'message'=>$result_json_data->message];
          }
      }else{
          $response = ['success'=>false, 'message'=>__('Reference field is required!')];
      }

      return response()->json($response);
  }
}
