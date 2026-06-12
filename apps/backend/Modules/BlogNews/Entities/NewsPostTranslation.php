<?php

namespace Modules\BlogNews\Entities;

use Illuminate\Database\Eloquent\Model;

class NewsPostTranslation extends Model
{
    protected $fillable = ['news_post_id', 'lang_key', 'title', 'body'];
}
