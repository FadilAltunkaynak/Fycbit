<?php

namespace Modules\KnowledgeBase\Entities;

use Illuminate\Database\Eloquent\Model;

class KnowledgebaseSupportSetting extends Model
{
    protected $fillable = ['slug','value'];
}
