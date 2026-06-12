<?php

namespace Modules\P2P\Http\Controllers;

use Illuminate\Routing\Controller;

class BaseController extends Controller
{
    public function sendBackResponse($response, $extra = [], $api = 0)
    {
        if ($api) {
            if(isset($extra['data']) && !$extra['data'] && isset($response['data'])){
                unset($response['data']);
            }
            $api_response = response()->json($response);
            return $api_response;
        }
        $redirect = redirect();
        $redirect = (isset($extra['route'])) ? $redirect->route($extra['route']) : $redirect->back();
        $message = ((isset($response['message'])) ? $response['message'] :(
            (isset($extra['message'])) ? $extra['message'] : ''
        ));
        if(isset($response['success']) && $response['success'])
        {
            $redirect = $redirect->with('success', $message);
            return $redirect;
        }
        $redirect = $redirect->with('dismiss', $message);
        return $redirect;
    }
}
