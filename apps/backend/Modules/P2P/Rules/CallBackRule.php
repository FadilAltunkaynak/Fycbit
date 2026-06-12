<?php

namespace Modules\P2P\Rules;

use Illuminate\Contracts\Validation\Rule;

class CallBackRule implements Rule
{
    public static $returnMessage;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(
        private $obj,
        private $method,
        private $message,
    )
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
        $method = $this->method;
        return ($this->obj)->$method();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return self::$returnMessage ?? $this->message;
    }
}
