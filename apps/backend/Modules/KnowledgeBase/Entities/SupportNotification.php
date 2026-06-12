<?php

namespace Modules\KnowledgeBase\Entities;

use Illuminate\Database\Eloquent\Model;

class SupportNotification extends Model
{
    protected $fillable = [
        'unique_code',
        'ticket_user_id',
        'ticket_id',
        'title',
        'body',
        'type',
        'get_notification_by',
        'status'
    ];
}
