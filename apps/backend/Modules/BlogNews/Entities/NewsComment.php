<?php

namespace Modules\BlogNews\Entities;

use Illuminate\Database\Eloquent\Model;

class NewsComment extends Model
{
    protected $fillable =  [ 'name', 'email', 'message', 'website', 'is_reply','reply_to', 'post_id', 'status' ];
}
