<?php

namespace Modules\BlogNews\Entities;

use Illuminate\Database\Eloquent\Model;

class BlogCategoryTranslation extends Model
{
    protected $fillable = ['blog_category_id', 'lang_key','title'];
}
