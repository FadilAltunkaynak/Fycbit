<?php

namespace Modules\BlogNews\Entities;

use Illuminate\Database\Eloquent\Model;

class BlogCategory extends Model
{
    protected $fillable = [ 'title', 'status', 'main_id', 'sub' ];

    public function translationCategory()
    {
        return $this->hasMany(BlogCategoryTranslation::class, 'blog_category_id');
    }
}
