<?php

namespace Modules\IcoLaunchpad\Entities;

use Illuminate\Database\Eloquent\Model;
use App\User;

class IcoToken extends Model
{
    protected $fillable = [
        'form_id', 'coin_type', 'base_coin', 'token_name', 'network', 'wallet_address', 'contract_address', 'wallet_private_key', 'chain_id', 'chain_link', 'decimal', 'gas_limit', 'user_id', 'status',
        'approved_id', 'approved_status', 'is_updated', 'website_link', 'details_rule','image_name', 'image_path', 'network_id'
    ];

    protected $hidden = [
        'wallet_private_key'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function icoPhaseDetails()
    {
        return $this->hasMany(IcoPhaseInfo::class, 'ico_token_id');
    }

    public function latestICOPhaseDetails()
    {
        return $this->hasMany(IcoPhaseInfo::class, 'ico_token_id')->latest()->take(1);
    }

    public function translationICO()
    {
        return $this->hasMany(ICOTokenTranslation::class,'ico_token_id');
    }
}
