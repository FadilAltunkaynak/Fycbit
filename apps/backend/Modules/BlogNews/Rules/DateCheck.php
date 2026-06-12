<?php

namespace Modules\BlogNews\Rules;

use Illuminate\Contracts\Validation\Rule;

class DateCheck implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        return date('Y-m-d H:i:s') <= date('Y-m-d H:i:s', strtotime($value)); 
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('Publish at date must be greater than current date');
    }
}
