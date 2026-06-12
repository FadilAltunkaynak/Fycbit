<?php

namespace Modules\KnowledgeBase\Entities;

use Illuminate\Database\Eloquent\Model;

class SupportCategory extends Model
{
    protected $fillable = ['unique_code','name','image','status'];
}
