<?php

namespace Modules\P2P\Entities;

use Illuminate\Database\Eloquent\Model;

class PGiftCardFeedback extends Model
{
    protected $fillable = ['order_id','user_id','to_user_id','feedback','feedback_type'];
}
