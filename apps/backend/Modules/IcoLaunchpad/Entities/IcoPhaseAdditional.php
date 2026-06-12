<?php

namespace Modules\IcoLaunchpad\Entities;

use Illuminate\Database\Eloquent\Model;

class IcoPhaseAdditional extends Model
{
    protected $fillable = ['ico_phase_id', 'title', 'value', 'file', 'is_updated'];
}
