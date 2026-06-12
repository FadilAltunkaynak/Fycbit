<?php

namespace Modules\KnowledgeBase\Entities;

use Illuminate\Database\Eloquent\Model;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;

class KnbCategory extends Model
{
    use HasEagerLimit;
    protected $fillable = ['unique_code','name', 'icon_class', 'description', 'status'];

    public function knbSubCategory()
    {
        return $this->hasMany(KnbSubCategory::class, 'category_id');
    }
}
