<?php

namespace Modules\P2P\Database\Seeders;

use App\Model\AdminSetting;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class AdminSettingSeederTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        // $kycList = [ 
        //     'p_phone_verification', 'p_email_verification', 'p_nid_verification',
        //     'p_passport_verification', 'p_driving_verification', 'p_voter_verification'
        // ];
        foreach(KYC_LIST_ARRAY as $kyc){
            AdminSetting::firstOrCreate(['slug'=>$kyc],['value'=>'0']);
        }
    }
}
