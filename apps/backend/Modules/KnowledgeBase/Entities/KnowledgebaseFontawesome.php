<?php

namespace Modules\KnowledgeBase\Entities;

use Illuminate\Database\Eloquent\Model;

class KnowledgebaseFontawesome extends Model
{
    protected $fillable = ['title','class_name','unicode'];
}
