<?php

namespace Modules\IcoLaunchpad\Entities;

use Illuminate\Database\Eloquent\Model;

class LaunchpadFeatureList extends Model
{
    protected $fillable = ['image', 'title', 'description', 'status', 'page_link', 'slug', 'page_type', 'custom_page_description'];
}
