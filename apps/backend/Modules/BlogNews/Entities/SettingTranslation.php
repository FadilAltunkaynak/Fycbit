<?php

namespace Modules\BlogNews\Entities;

use Illuminate\Database\Eloquent\Model;

class SettingTranslation extends Model
{
    protected $fillable = ['lang_key','key','slug','value'];
}
