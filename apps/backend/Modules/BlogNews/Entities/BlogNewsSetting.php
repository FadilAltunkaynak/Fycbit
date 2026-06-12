<?php

namespace Modules\BlogNews\Entities;

use Illuminate\Database\Eloquent\Model;

class BlogNewsSetting extends Model
{
    protected $fillable = [ 'slug', 'value' ];

    public function translationSettings()
    {
        return $this->hasMany(SettingTranslation::class, 'slug','slug');
    }
}
