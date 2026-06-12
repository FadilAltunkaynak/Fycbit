<?php

namespace Modules\KnowledgeBase\Entities;

use Illuminate\Database\Eloquent\Model;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;
use App\User;
class SupportTicket extends Model
{
    use HasEagerLimit;
    protected $fillable = ['user_id', 'category_id', 'project_id', 'title', 'description', 'status','is_seen_by_user','assigned_agent_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function last_conversation()
    {
        return $this->hasOne(SupportConversation::class, 'ticket_id')->where('conversation_type',CONVERSATION_TYPE_RAGULAR)->latest()->limit(1);
    }

    public function category()
    {
        return $this->belongsTo(SupportCategory::class, 'category_id');
    }

    public function project()
    {
        return $this->belongsTo(SupportProject::class, 'project_id');
    }
}
