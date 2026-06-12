<?php

namespace Modules\P2P\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckMultiData implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(
        private $class,
        private $column,
        private $message,
        private $is_all = false,
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
        if($this->is_all && ($value == "all")) return true;
        $value = explode(",",$value);
        foreach ($value as $value) {
            $check = $this->class::where([$this->column => $value, "status" => STATUS_ACTIVE])->first();
            if(empty($check)) return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
