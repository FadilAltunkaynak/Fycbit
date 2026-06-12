<?php

namespace Modules\BlogNews\Entities;

use Illuminate\Database\Eloquent\Model;

class NewsCategory extends Model
{
    protected $fillable = [ 'title', 'status', 'main_id', 'sub' ];

    public function translationCategory()
    {
        return $this->hasMany(NewsCategoryTranslation::class, 'news_category_id');
    }
}
