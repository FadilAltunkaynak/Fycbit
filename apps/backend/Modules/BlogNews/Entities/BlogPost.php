<?php

namespace Modules\BlogNews\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\BlogNews\Entities\BlogCategory;

class BlogPost extends Model
{
    protected $fillable = [
        'title' ,'slug' , 'thumbnail' , 'category' , 
        'sub_category' , 'status' , 'body' , 
        'keywords','description','publish' , 'views',
        'is_fetured','publish_at','comment_allow'
    ];

    public function translationBlogPost()
    {
        return $this->hasMany(BlogPostTranslation::class, 'blog_post_id');
    }

    public function main_category()
    {
        return $this->belongsTo(BlogCategory::class, 'category');
    }
}
