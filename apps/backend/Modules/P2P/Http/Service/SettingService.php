<?php

namespace Modules\P2P\Http\Service;

use Illuminate\Support\Facades\DB;
use Modules\P2P\Http\Repository\SettingRepository;

class SettingService {

    private $repo;
    private $languageService;
    public function __construct(){
        $this->repo = new SettingRepository();
    }

    public function settingsUpdate($request)
    {
        try{
            $data = [];
            foreach($request->except(['_token','tab']) as $slug => $value){
                $data[] = [
                    'slug' => $slug ,
                    'value'=> $value
                ];
            }
            return $this->repo->settingSaveUpdate($data);
        } catch (\Exception $e) {
            storeException('settingsUpdate',$e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }
}
