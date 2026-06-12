<?php

namespace Modules\BlogNews\Rules;

use Modules\BlogNews\Entities\BlogPost;
use Modules\BlogNews\Entities\NewsPost;
use Illuminate\Contracts\Validation\Rule;

class CheckEncryptPostValue implements Rule
{
    private $postModel;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($type)
    {
        $this->postModel = $type == 'blog' ? BlogPost::class : NewsPost::class;
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
            $post = $this->postModel::find($value);
            if ($post) return true;
            return false;
        }catch (\Exception $e){
            storeException('CheckEncryptPostValue',$e->getMessage());
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
        return __("Post id is invalid");
    }
}
