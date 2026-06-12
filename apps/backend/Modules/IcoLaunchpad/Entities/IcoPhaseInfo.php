<?php

namespace Modules\IcoLaunchpad\Entities;

use Illuminate\Database\Eloquent\Model;

class IcoPhaseInfo extends Model
{
    protected $fillable = [
        'user_id', 'ico_token_id', 'coin_price', 'coin_currency', 'total_token_supply', 'available_token_supply', 'phase_title', 'start_date',
        'end_date', 'description', 'video_link', 'social_link', 'image', 'status', 'is_updated', 'is_featured',
        'minimum_purchase_price', 'maximum_purchase_price'
    ];

    public function icoPhaseAdditionalDetails()
    {
        return $this->hasMany(IcoPhaseAdditional::class, 'ico_phase_id');
    }
}
