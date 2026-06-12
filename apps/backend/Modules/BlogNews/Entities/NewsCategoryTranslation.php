<?php

namespace Modules\BlogNews\Entities;

use Illuminate\Database\Eloquent\Model;

class NewsCategoryTranslation extends Model
{
    protected $fillable = ['news_category_id', 'lang_key','title'];
}
