<?php

namespace Modules\KnowledgeBase\Entities;

use Illuminate\Database\Eloquent\Model;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;

class KnbSubCategory extends Model
{
    use HasEagerLimit;
    protected $fillable = ['category_id','unique_code','name', 'icon_class', 'total_artical', 'status'];

    public function category()
    {
        return $this->belongsTo(KnbCategory::class, 'category_id');
    }

    public function knbArticles()
    {
        return $this->hasMany(KnbArticle::class, 'sub_category_id');
    }
}
