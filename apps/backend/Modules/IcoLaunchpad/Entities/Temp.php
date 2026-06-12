<?php

namespace Modules\IcoLaunchpad\Entities;

use Illuminate\Database\Eloquent\Model;

class Temp extends Model
{
    protected $fillable = ['update_table_type', 'update_table_type_id', 'column_name', 'previous_value', 'requested_value', 'status'];
}
