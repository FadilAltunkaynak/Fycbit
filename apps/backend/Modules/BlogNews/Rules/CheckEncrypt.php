<?php

namespace Modules\BlogNews\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckEncrypt implements Rule
{
    private $model;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        try{
            $value = decryptId($value);
            if (isset($value['success'])) return false;
            $comment = $this->model::find($value);
            if ($comment) return true;
            return false;
        }catch (\Exception $e){
            storeException('CheckEncrypt',$e->getMessage());
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The validation error message.';
    }
}
