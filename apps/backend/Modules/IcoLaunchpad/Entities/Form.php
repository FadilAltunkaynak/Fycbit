<?php

namespace Modules\IcoLaunchpad\Entities;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    protected $fillable = ['name', 'title', 'type', 'required', 'is_option', 'optionList', 'is_file', 'file_type', 'file_link'];
}
