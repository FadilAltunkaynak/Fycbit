<?php
 
namespace Modules\P2P\Casts;
 
use App\Model\CountryList;
use Illuminate\Database\Eloquent\Model;
use Modules\P2P\Entities\PPaymentTime;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
 
class TimeCast
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function get(Model $model, string $key, mixed $value, array $attributes)
    {
        // dd($key,$value);
        if($a = PPaymentTime::where('uid', $value)->first()){
            return [
                'uid' => $a->uid,
                'value' => $a->time,
            ];
        }
        return [
            'uid' => '',
            'value' => '',
        ];
    }
 
    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return json_encode($value);
    }
}