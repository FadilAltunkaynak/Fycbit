<?php

namespace Modules\KnowledgeBase\Entities;

use Illuminate\Database\Eloquent\Model;

class KnbArticleSection extends Model
{
    protected $fillable = ['unique_code','article_id','title','icon_class','description','status'];
}
