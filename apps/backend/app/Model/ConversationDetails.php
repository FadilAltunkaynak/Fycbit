<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ConversationDetails extends Model
{
    protected $fillable = ['sender_id','receiver_id', 'conversation_type','conversation_type_id','message',
                            'file_name','is_seen'];
}
