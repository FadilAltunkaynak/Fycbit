<?php

namespace Modules\P2P\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\P2P\Entities\PLandingHowTo;
use Modules\P2P\Http\Service\LandingService;
use Modules\P2P\Http\Controllers\BaseController;

class LandingSettingsController extends BaseController
{
    private $service;
    public function __construct()
    {
        $this->service = new LandingService;
    }

    public function getLandingSettings()
    {
        $data = [];
        try {
            $data['title'] = __("Landing Header Settings");
            $data['adm_setting'] = settings(['p2p_banner_img','p2p_banner_des','p2p_banner_header']);
        } catch (\Exception $e) {
            storeException("getLandingSettings", $e->getMessage());
        }
        return view('p2p::landing.heading', $data);
    }

    public function setLandingSettings(Request $request)
    {
        try {
            $response = $this->service->setLandingHeadingSettings($request);
            return $this->sendBackResponse($response);
        } catch (\Exception $e) {
            storeException("getLandingSettings", $e->getMessage());
            return response()->json(responseData(false, __("Something went wrong")));
        }

    }

    public function getLandingHowToP2p(Request $request)
    {
        $data['title'] = __("How P2P Work Settings");
        $data['tab'] = $request->tab ?? 'buy';
        return view('p2p::landing.how_to.how_to',$data);
    }

    public function setLandingHowToP2p(Request $request)
    {
        try {
            $response = $this->service->saveAdminSetting($request);
            if($response['success'])
            {
                return back()->with('success', $response['message']);
            }else{
                return back()->with('dismiss', $response['message']);
            }
        } catch (\Exception $e) {
            storeException("getLandingSettings", $e->getMessage());
            return response()->json(responseData(false, __("Something went wrong")));
        }
    }

    public function getLandingHowToP2pData(Request $request)
    {
        try {
            if(isset($request->type)){
                $data = PLandingHowTo::where('type', $request->type)->get();
                return datatables()->of($data)
                ->editColumn('header',fn($item)=> $item->header)
                ->editColumn('description',fn($item)=> $item->description)
                ->editColumn('image',fn($item)=> '<img src="'.asset(LANDING_PAGE_PATH.$item->image).'" width="60px" height="50px" />')
                ->rawColumns(['image'])
                ->make();
            }
            return $this->sendBackResponse(responseData(false, __("No data found")),[],true);
        } catch (\Exception $e) {
            storeException("getLandingSettings", $e->getMessage());
            return response()->json(responseData(false, __("Something went wrong")));
        }
    }

    public function getLandingHowToP2pAdd(Request $request)
    {
        $data = [];
        try {
            $data['type'] = $request->type ?? 'buy';
            if(isset($request->uid) && $how = PLandingHowTo::where('uid', $request->uid)->first())
            $data['data'] = $how;
        } catch (\Exception $e) {
            storeException("getLandingSettings", $e->getMessage());
        }
        return view('p2p::landing.how_to.addEdit', $data);
    }

    public function landingAdvantageP2p()
    {
        $data['title'] = __("Advantage Of P2P Exchange Settings");
        return view('p2p::landing.advantage.setting',$data);
    }

}


