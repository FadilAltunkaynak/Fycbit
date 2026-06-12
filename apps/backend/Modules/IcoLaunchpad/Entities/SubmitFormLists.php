<?php

namespace Modules\IcoLaunchpad\Entities;

use Illuminate\Database\Eloquent\Model;
use App\User;

class SubmitFormLists extends Model
{
    protected $fillable = ['user_id', 'unique_id', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function formDetails()
    {
        return $this->hasMany(SubmitFormDetails::class, 'unique_id', 'unique_id');
    }
}
