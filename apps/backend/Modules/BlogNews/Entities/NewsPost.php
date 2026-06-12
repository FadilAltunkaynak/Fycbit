<?php

namespace Modules\BlogNews\Entities;

use Illuminate\Database\Eloquent\Model;

class NewsPost extends Model
{
    protected $fillable = [
        'title', 'slug', 'thumbnail', 'category', 
        'sub_category', 'status', 'body', 
        'keywords', 'description', 'publish', 'views',
        'is_fetured', 'publish_at', 'comment_allow'
    ];

    public function translationNewsPost()
    {
        return $this->hasMany(NewsPostTranslation::class, 'news_post_id');
    }
}
