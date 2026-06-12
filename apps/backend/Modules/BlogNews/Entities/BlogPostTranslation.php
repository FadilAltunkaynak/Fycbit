<?php

namespace Modules\BlogNews\Entities;

use Illuminate\Database\Eloquent\Model;

class BlogPostTranslation extends Model
{
    protected $fillable = ['blog_post_id', 'lang_key', 'title', 'body'];
}
