<?php

namespace Modules\P2P\Http\Controllers\Api;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Modules\P2P\Entities\POrder;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\P2P\Http\Service\UserService;

class UserController extends Controller
{
    private $service;
    public function __construct(){
        $this->service = new UserService();
    }

    public function userCenter()
    {
        try{
            $response = $this->service->userCenter();
            return response()->json($response);
        }catch(\Exception $e){
            storeException("userCenter", $e->getMessage());
            return  response()->json(responseData(false, __("Something went wrong")));
        }
    }

    public function userProfile(Request $request)
    {
        try{
            if(!isset($request->id)) return response()->json(responseData(false, __("User id is required")));
            if($user = User::find($request->id)){
                $response = $this->service->userProfile($user);
                return response()->json($response);
            }
            return response()->json(responseData(false, __("User not found")));
        }catch(\Exception $e){
            storeException("userCenter", $e->getMessage());
            return  response()->json(responseData(false, __("Something went wrong")));
        }
    }
}
