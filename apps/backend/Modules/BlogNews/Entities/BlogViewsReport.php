<?php

namespace Modules\BlogNews\Entities;

use Illuminate\Database\Eloquent\Model;

class BlogViewsReport extends Model
{
    protected $fillable = ['day','date','count'];
}
