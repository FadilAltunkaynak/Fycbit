<?php

namespace Modules\IcoLaunchpad\Entities;

use Illuminate\Database\Eloquent\Model;

class ICOTokenTranslation extends Model
{
    protected $fillable = ['ico_token_id','lang_key','details_rule'];
}
