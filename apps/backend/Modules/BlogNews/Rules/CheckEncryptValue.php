<?php

namespace Modules\BlogNews\Rules;

use Illuminate\Contracts\Validation\Rule;
use Modules\BlogNews\Entities\BlogComment;
use Modules\BlogNews\Entities\NewsComment;

class CheckEncryptValue implements Rule
{
    private $model;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($type)
    {
        $this->model = $type == 'blog' ? BlogComment::class : NewsComment::class;
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
            storeException('CheckEncryptValue',$e->getMessage());
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
        return __('The comment id is invalid');
    }
}
