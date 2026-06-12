<?php

namespace Modules\IcoLaunchpad\Entities;

use Illuminate\Database\Eloquent\Model;

class SubmitFormDetails extends Model
{
    protected $fillable = ['unique_id', 'question', 'is_input', 'answer', 'is_option', 'is_file'];
}
