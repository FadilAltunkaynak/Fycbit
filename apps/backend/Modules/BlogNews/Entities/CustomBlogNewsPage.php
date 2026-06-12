<?php

namespace Modules\BlogNews\Entities;

use Illuminate\Database\Eloquent\Model;

class CustomBlogNewsPage extends Model
{
    protected $fillable = [ 'title', 'type', 'slug', 'body', 'status' ];
}
