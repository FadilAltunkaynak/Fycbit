<?php

namespace Modules\KnowledgeBase\Entities;

use Illuminate\Database\Eloquent\Model;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;

class KnbArticle extends Model
{
    use HasEagerLimit;
    protected $fillable = ['unique_code','icon_class','category_id','sub_category_id','title','description','feature_image','status'];

    public function knbArticleSections()
    {
        return $this->hasMany(KnbArticleSection::class, 'article_id');
    }
}
