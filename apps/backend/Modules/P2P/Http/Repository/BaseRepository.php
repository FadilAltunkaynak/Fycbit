<?php
namespace Modules\P2P\Http\Repository;

use ReflectionClass;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\CommonRepository;

class BaseRepository
{
    public function createOrUpdate($model, $query)
    {
        try {
            $model = $this->getModel($model);
            $find = $this->getFinder($query);//find
            $data = $this->getupdateOrCreateData($query);//data
            $result = $model->updateOrCreate($find, $data);
            if($result) return responseData(true,__('Successfully'),$result);
            return responseData(false,__('Failed'));
        } catch (\Exception $e) {
            storeException('createOrUpdate BaseRepository', $e->getMessage());
            return responseData(false,__('Something went wrong'));
        }
    }

    private function getFinder($query)
    {
        try {
            $find = [];
            if(isset($query['find']))
                $find = $query['find'];
            else
                $find['id'] = 0;
            return $find;
        } catch (\Exception $e) {
            storeException('getFinder BaseRepository', $e->getMessage());
            return ['id' => 0];
        }
    }
    private function getupdateOrCreateData($query)
    {
        try {
            $data = [];
            if(isset($query['data']))
                $data = $query['data'];
            return $data;
        } catch (\Exception $e) {
            storeException('getFinder BaseRepository', $e->getMessage());
            return [];
        }
    }
    private function getModel($model)
    {
        try {
            $reflection = new ReflectionClass($model);
            $class = $reflection->getName();
            return new $class;
        } catch (\Exception $e) {
            storeException('getModel BaseRepository', $e->getMessage());
            return null;
        }
    }

    public function getModelData($model, $query)
    {
        try {
            DB::beginTransaction();
            $model = $this->getModel($model);
            $data = $model;
            foreach ($query as $key => $value) {
                if(is_numeric($key)){
                    if(is_array($value)){
                        foreach (array_keys($value) as $k) 
                        {
                            if(is_array($value[$k]) && !empty($value[$k])) $data = $data->$k(...$value[$k]);
                            else $data = $data->$k();
                        }
                    }
                }else{
                    if(is_array($value) && !empty($value)) $data = $data->$key(...$value);
                    else $data = $data->$key();
                }
            }
            if($data){
                DB::commit();
                return responseData(true, __('Success'), $data);
            }
            DB::rollBack();
            return responseData(false, __('Failed'));
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('getModelData', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }
}