<?php

namespace Modules\KnowledgeBase\Entities;

use Illuminate\Database\Eloquent\Model;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;
use App\User;

class SupportConversation extends Model
{
    use HasEagerLimit;
    protected $fillable = ['ticket_id', 'message', 'conversation_type', 'has_file', 'user_id'];

    public function conversationAttachment()
    {
        return $this->hasMany(SupportConversationAttachment::class, 'conversation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
