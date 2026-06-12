<?php

namespace Modules\P2P\Rules;

use Illuminate\Contracts\Validation\Rule;

class EqualTo implements Rule
{
    private $model;
    private $compere;
    private $column;
    private $value;
    private $message;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($model,$compere,$column,$value,$message)
    {
        $this->model = $model;
        $this->compere = $compere;
        $this->column = $column;
        $this->value = $value;
        $this->message = $message;
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
        $data = $this->model::where($this->column,$this->compere,$value)->first();
        if($data){
            if(gettype($this->value) == 'array'){
                if($data->{$this->value[0]} == $this->value[1]) return 1;
            }else{
                if($data->trade_status == $this->value) return 1;
            }
        }
        return 0;
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
