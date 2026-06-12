<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('future_trade_{user_id}', fn($user, $user_id)=> $user->id == $user_id );