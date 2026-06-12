<?php

namespace Modules\BlogNews\Entities;

use Illuminate\Database\Eloquent\Model;

class NewsViewsReport extends Model
{
    protected $fillable = ['day','date','count'];
}
