<?php

namespace Modules\KnowledgeBase\Entities;

use Illuminate\Database\Eloquent\Model;

class SupportConversationAttachment extends Model
{
    protected $fillable = ['conversation_id', 'file_link'];
}
