<?php

namespace Modules\IcoLaunchpad\Http\Services;

use App\Model\Coin;
use Modules\IcoLaunchpad\Http\Repositories\IcoPhaseRepository;
use Modules\IcoLaunchpad\Http\Repositories\IcoPhaseAdditionalRepository;
use Modules\IcoLaunchpad\Entities\IcoToken;
use Modules\IcoLaunchpad\Entities\IcoPhaseInfo;
use Modules\IcoLaunchpad\Entities\IcoPhaseAdditional;

class IcoPhaseService
{

    private $icoPhaseRepository;
    private $icoPhaseAdditionalRepository;

    public function __construct()
    {
        $this->icoPhaseRepository = new IcoPhaseRepository();
        $this->icoPhaseAdditionalRepository = new IcoPhaseAdditionalRepository();
    }

    public function getICOPhaseActiveList($request)
    {
        try {
            $response = $this->icoPhaseRepository->getICOPhaseActiveList($request);
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("getICOPhaseActiveList", $e->getMessage());
        }
        return $response;
    }

    public function getActiveICOPhaseDetails($request, $extra = null)
    {
        try {
            $response = $this->icoPhaseRepository->getActiveICOPhaseDetails($request, $extra);
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("getActiveICOPhaseDetails", $e->getMessage());
        }
        return $response;
    }

    public function getICOPhasesList($icoTokenId, $per_page = null)
    {
        try {
            $response = $this->icoPhaseRepository->getICOPhasesList($icoTokenId, $per_page);
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("storeNewICO", $e->getMessage());
        }
        return $response;
    }

    public function saveICOPhase($request)
    {
        try {
            $user = auth()->user();
            $check_listed_coin = Coin::where('ico_id',$request->ico_token_id)->where('is_listed',1)->first();
            if(isset($check_listed_coin))
            {
                return responseData(false, __('You can not created new phase for this token as this token is listed!'));
            }
            if ($user->role != USER_ROLE_ADMIN) {
                $ico_token_details = IcoToken::where('user_id', $user->id)->where('id', $request->ico_token_id)->first();

                if (isset($ico_token_details)) {
                    if ($ico_token_details->approved_status != STATUS_ACCEPTED) {
                        $response = ['success' => true, 'message' => __('You can create a ICO Token phase after your ICO Token is accepted')];
                        return $response;
                    }
                } else {
                    $response = ['success' => true, 'message' => __('ICO Token is not found!')];
                    return $response;
                }
            }
            $data = [
                'ico_token_id' => $request->ico_token_id,
                'user_id' => $user->id,
                'coin_price' => $request->coin_price,
                'coin_currency' => $request->coin_currency,
                'total_token_supply' => $request->total_token_supply,
                'phase_title' => $request->phase_title,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'description' => $request->description,
                'video_link' => $request->video_link,
                'minimum_purchase_price'=> $request->minimum_purchase_price??0,
                'maximum_purchase_price'=> $request->maximum_purchase_price??0
            ];

            $social_media = [];
            if (isset($request->social_link)) {
                foreach ($request->social_link as $key => $link) {
                    $social_media[socialMediaList($key)] = $link;
                }
                $data['social_link'] = json_encode($social_media);
            }

            $id = null;
            $old_image = null;
            if (isset($request->id)) {
                $id = $request->id;
                $ico_phase_info = IcoPhaseInfo::find($id);
                $old_image = isset($ico_phase_info) ? $ico_phase_info->image : null;
            } else {
                $data['available_token_supply'] = $request->total_token_supply;
            }

            if (!empty($request->image)) {
                $imageName = uploadAnyFile($request->image, FILE_ICO_STORAGE_PATH, $old_image);
                $data['image'] = $imageName;
            }



            $response_ico_phase = $this->icoPhaseRepository->storeUpdateICOPhase($id, $data);

            if ($response_ico_phase['success'] == true) {
                $ico_phase_details_id = isset($id) ? $id : $response_ico_phase['data']->id;

                if (isset($request->additional)) {
                    $additional = [];
                    foreach ($request->additional as $itemAdditional) {
                        $temp_additional = [];
                        $ico_additional_phase_id = null;
                        foreach ($itemAdditional as $itm_k => $itm) {
                            $old_image_additional = null;
                            if ($itm_k == 'id') {
                                $ico_additional_phase_id = $itm;
                                $tempAdditional = IcoPhaseAdditional::find($ico_additional_phase_id);
                                $old_image_additional = isset($tempAdditional) ?  $tempAdditional->file : null;
                            }

                            if ($itm_k == 'file' && is_file($itemAdditional[$itm_k])) {
                                $imageName = uploadAnyFile($itemAdditional[$itm_k], FILE_ICO_STORAGE_PATH, $old_image_additional);
                                $temp_additional[$itm_k] = $imageName;
                            } else if ($itm_k != 'file' && !empty($itm)) {
                                $temp_additional[$itm_k] = $itm;
                            }
                        }
                        $temp_additional['ico_phase_id'] = $ico_phase_details_id;
                        $response_ico_additional_info = $this->icoPhaseAdditionalRepository->saveAddtionaPhaseInfo($temp_additional, $ico_additional_phase_id);

                        if ($response_ico_additional_info['success'] != true) {
                            return $response_ico_additional_info;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $response_ico_phase = ['success' => false, 'message' => __('Something went wrong')];
            storeException("storeUpdateICO", $e->getMessage());
        }
        return $response_ico_phase;
    }

    public function saveICOPhaseAPI($request)
    {
        try {
            $user = auth()->user();

            $check_listed_coin = Coin::where('ico_id',$request->ico_token_id)->where('is_listed',1)->first();
            if(isset($check_listed_coin))
            {
                return responseData(false, __('You can not created new phase for this token as this token is listed!'));
            }
            if ($user->role != USER_ROLE_ADMIN) {
                $ico_token_details = IcoToken::where('user_id', $user->id)->where('id', $request->ico_token_id)->first();

                if (isset($ico_token_details)) {
                    if ($ico_token_details->approved_status != STATUS_ACCEPTED) {
                        $response = ['success' => false, 'message' => __('You can create a ICO Token phase after your ICO Token is accepted')];
                        return $response;
                    }
                } else {
                    $response = ['success' => false, 'message' => __('ICO Token is not found!')];
                    return $response;
                }
            }
            $data = [
                'ico_token_id' => $request->ico_token_id,
                'user_id' => $user->id,
                'coin_price' => $request->coin_price,
                'coin_currency' => $request->coin_currency,
                'total_token_supply' => $request->total_token_supply,
                'phase_title' => $request->phase_title,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'description' => $request->description,
                'video_link' => $request->video_link,
                'minimum_purchase_price'=> $request->minimum_purchase_price??0,
                'maximum_purchase_price'=> $request->maximum_purchase_price??0
            ];

            $social_media = [];
            if (isset($request->social_link)) {
                foreach ($request->social_link as $key => $link) {
                    $social_media[socialMediaList($key)] = $link;
                }
                $data['social_link'] = json_encode($social_media);
            }

            $id = null;
            $old_image = null;
            if (isset($request->id) && $request->id != "null") {
                $id = $request->id;
                $ico_phase_info = IcoPhaseInfo::find($id);
                $old_image = isset($ico_phase_info) ? $ico_phase_info->image : null;
            } else {
                $data['available_token_supply'] = $request->total_token_supply;
            }

            if (!empty($request->image)) {
                $imageName = uploadAnyFile($request->image, FILE_ICO_STORAGE_PATH, $old_image);
                $data['image'] = $imageName;
            }

            if (isset($request->id) && $request->id != "null" && $user->role != USER_ROLE_ADMIN) {
                $response_ico_phase = $this->icoPhaseRepository->storeUpdateRequestICOPhase($id, $data);
            } else {
                $response_ico_phase = $this->icoPhaseRepository->storeUpdateICOPhase($id, $data);
            }
        } catch (\Exception $e) {
            $response_ico_phase = ['success' => false, 'message' => __('Something went wrong')];
            storeException("saveICOPhaseAPI", $e->getMessage());
        }
        return $response_ico_phase;
    }

    public function getICOPhaseDetailsById($icoPhaseId)
    {
        try {
            $response = $this->icoPhaseRepository->getICOPhaseDetailsById($icoPhaseId);
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("storeNewICO", $e->getMessage());
        }
        return $response;
    }

    public function deleteICOPhaseById($icoPhaseId)
    {
        try {
            $response = $this->icoPhaseRepository->deleteICOPhaseById($icoPhaseId);
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("storeNewICO", $e->getMessage());
        }
        return $response;
    }

    public function checkDateOfPhase($request)
    {
        try {
            $response = $this->icoPhaseRepository->checkDateOfPhase($request);
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong!')];
            storeException("checkDateOfPhase", $e->getMessage());
        }
        return $response;
    }

    public function changeICOPhaseFeatured($id)
    {
        try {
            $response = $this->icoPhaseRepository->changeICOPhaseFeatured($id);
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("changeICOPhaseStatus", $e->getMessage());
        }
        return $response;
    }

    public function changeICOPhaseStatus($id)
    {
        try {
            $response = $this->icoPhaseRepository->changeICOPhaseStatus($id);
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("changeICOPhaseStatus", $e->getMessage());
        }
        return $response;
    }
}
