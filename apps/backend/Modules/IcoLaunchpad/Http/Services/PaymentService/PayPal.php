<?php

namespace Modules\IcoLaunchpad\Http\Services\PaymentService;

class PayPal
{
    protected $clientID;
    protected $Secret;
    protected $type;
    protected $access;
    protected $id;

    public function __construct()
    {
    }
    public function getAccess()
    {
        $setting = settings(['paypal_client_id', 'paypal_secret_code']);
        $response = $this->getAuthenticationAccess($setting['paypal_client_id'], $setting['paypal_secret_code']);
        if (isset($response['access_token'])) {
            $response['app_id'] = $response['app_id'] . uniqid();
            session([
                'access_token' . auth()->user()->id => $response['access_token'],
                'app_id' . auth()->user()->id => $response['app_id'],
                'token_type' . auth()->user()->id => $response['token_type']
            ]);
            $this->type = $response['token_type'];
            $this->access = $response['access_token'];
            $this->id = $response['app_id'];
            return ['success' => true, 'message' => __('Accessed successfully !')];
        }
        return ['success' => false, 'message' => isset($response['message']) ? $response['message'] :  __('No paypal access!')];
    }

    private function getAuthenticationAccess($client_id, $secret)
    {
        return $this->execute(
            "v1",
            "oauth2/token?grant_type=client_credentials",
            "post",
            [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json'
            ],
            null,
            "$client_id:$secret"
        );
    }

    public function createOrder($price)
    {
        if ($price <= 0) return [];
        $response = $this->getAccess();
        if (!$response['success']) return $response['message'];
        $form_data = [
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "reference_id" => "d9f80740-38f0-11e8-b467-0ed5f89f718bb",
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => number_format($price, 2, '.', '')
                    ]
                ]
            ],
            "payment_source" => [
                "paypal" => [
                    "experience_context" => [
                        "return_url" => route("PaypalSuccess"),
                        "cancel_url" => route("PaypalCancel")
                    ]
                ]
            ],

        ];
        return $this->execute(
            "v2",
            "checkout/orders",
            "post",
            [
                'Content-Type: application/json',
                "Authorization: $this->type $this->access",
                "PayPal-Request-Id: $this->id"
            ],
            json_encode($form_data)
        );
    }

    public function paypalComplete($request)
    {
        return $this->execute(
            "v2",
            "checkout/orders/$request->token/capture",
            "post",
            [
                'Content-Type: application/json',
                "Authorization: " . session()->pull("token_type" . auth()->user()->id) . " " . session()->pull("access_token" . auth()->user()->id),
                "PayPal-Request-Id: " . session()->pull("app_id" . auth()->user()->id)
            ]
        );
    }


    private function execute($v, $endPoint, $method, $headers, $postData = null, $user = null)
    {
        try {
            $mode = settings('paypal_mode');
            $mode = $mode !== false ? $mode : 'test';
            $url = '';
            if ($mode == 'test')
                $url = "https://api-m.sandbox.paypal.com/$v/";
            else
                $url = "https://api-m.paypal.com/$v/";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url . $endPoint);
            if ($headers) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, $method == "post" ? 1 : 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if ($user) curl_setopt($ch, CURLOPT_USERPWD, $user);
            if ($postData) curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

            $response = curl_exec($ch);
            curl_close($ch);
            return json_decode($response, 1);
        } catch (\Exception $exception) {
            return ['success' => false, "message" => __($exception->getMessage())];
        }
    }
}
