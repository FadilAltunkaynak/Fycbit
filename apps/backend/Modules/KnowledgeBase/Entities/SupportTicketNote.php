<?php

namespace Modules\KnowledgeBase\Entities;

use Illuminate\Database\Eloquent\Model;

class SupportTicketNote extends Model
{
    protected $fillable = ['unique_code','ticket_id','user_id','user_type','notes'];
}
