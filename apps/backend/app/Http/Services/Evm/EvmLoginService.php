<?php

namespace App\Http\Services\Evm;

use Illuminate\Support\Facades\Auth;

class EvmLoginService
{
    private static $host;
    private static $api_secret;

    public function __construct(){
        self::$host = env('EVM_APP_HOST');
        self::$api_secret = env('EVM_APP_SECRET');
    }

    public function logIn()
    {
        $body = [
            'email' => Auth::user()->email
        ];
        return $response = $this->__send('/auth/login', $body, 'post');
    }

    private function __send($url, $body = [], $method = 'GET')
    {
        try {
            $header = [
                "Accept: application/json;",
                "evmapisecret: " . self::$api_secret,
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, self::$host.$url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

            if($method != 'GET') {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($body));
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $res = curl_exec($ch);
            curl_close($ch);
            return json_decode($res,1);
        } catch (\Exception $e) {
            storeException("EvmLoginService __send", $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

}
