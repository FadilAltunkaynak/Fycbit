<?php
namespace Modules\P2P\Http\Service;

use App\Model\AdminSetting;
use App\Model\Wallet;
use Modules\P2P\Entities\P2PWallet;
use Modules\P2P\Entities\PLandingHowTo;
use Modules\P2P\Jobs\WalletBalanceTransfer;
use Illuminate\Support\Facades\DB;

class LandingService
{
    public function setLandingHeadingSettings($request)
    {
        try {
            if(isset($request->p2p_banner_header))
            AdminSetting::updateOrCreate(['slug'=>'p2p_banner_header'],['value' => $request->p2p_banner_header]);
            if(isset($request->p2p_banner_des))
            AdminSetting::updateOrCreate(['slug'=>'p2p_banner_des'],['value' => $request->p2p_banner_des]);
            if(isset($request->p2p_banner_img) && $request->hasFile('p2p_banner_img')){
                $image = '';
                if($imageOld = AdminSetting::where('slug', 'p2p_banner_img')->first())
                    $image = uploadFilep2p($request->p2p_banner_img, P2P_LANDING_PATH, $imageOld->value);
                else
                    $image = uploadFilep2p($request->p2p_banner_img, P2P_LANDING_PATH);
                AdminSetting::updateOrCreate(['slug'=>'p2p_banner_img'],['value' => $image]);
            }
            return responseData(true, __("Setting has been successfully updated"));
        } catch (\Exception $e) {
            storeException('setLandingSettings', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function saveAdminSetting($request)
    {
        $response = ['success' => false, 'message' => __('Invalid request')];
        DB::beginTransaction();
        try {
            foreach ($request->except('_token') as $key => $value) {
                if ($request->hasFile($key)) {
                    $image = uploadFilep2p($request->$key, P2P_LANDING_PATH, settings($key) ?? "");
                    AdminSetting::updateOrCreate(['slug' => $key], ['value' => $image]);
                } else {
                    AdminSetting::updateOrCreate(['slug' => $key], ['value' => $value]);
                }
            }

            $response = [
                'success' => true,
                'message' => __('Setting updated successfully')
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'success' => false,
                'message' => __('Something went wrong')
            ];
            return $response;
        }
        DB::commit();
        return $response;
    }

}
