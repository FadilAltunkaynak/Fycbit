<?php

namespace Modules\BlogNews\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\BlogNews\Entities\BlogCategory;

class BlogCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        BlogCategory::firstOrCreate(['title' => "News and Analysis",], ['sub' => STATUS_INACTIVE, 'status' => STATUS_ACTIVE]);
        BlogCategory::firstOrCreate(['title' => "Investment Strategies",], ['sub' => STATUS_INACTIVE, 'status' => STATUS_ACTIVE]);
        BlogCategory::firstOrCreate(['title' => "Blockchain Technology",], ['sub' => STATUS_INACTIVE, 'status' => STATUS_ACTIVE]);

        BlogCategory::firstOrCreate(['title' => "Market trends and analysis",], ['sub' => STATUS_ACTIVE, 'main_id' => 1, 'status' => STATUS_ACTIVE]);
        BlogCategory::firstOrCreate(['title' => "Regulatory updates",], ['sub' => STATUS_ACTIVE, 'main_id' => 1, 'status' => STATUS_ACTIVE]);
        BlogCategory::firstOrCreate(['title' => "Major news and announcements",], ['sub' => STATUS_ACTIVE, 'main_id' => 1, 'status' => STATUS_ACTIVE]);

        BlogCategory::firstOrCreate(['title' => "Beginner guides to investing in cryptocurrency",], ['sub' => STATUS_ACTIVE, 'main_id' => 2, 'status' => STATUS_ACTIVE]);
        BlogCategory::firstOrCreate(['title' => "Technical analysis",], ['sub' => STATUS_ACTIVE, 'main_id' => 2, 'status' => STATUS_ACTIVE]);
        BlogCategory::firstOrCreate(['title' => "Portfolio diversification",], ['sub' => STATUS_ACTIVE, 'main_id' => 2, 'status' => STATUS_ACTIVE]);

        BlogCategory::firstOrCreate(['title' => "How blockchain technology works",], ['sub' => STATUS_ACTIVE, 'main_id' => 3, 'status' => STATUS_ACTIVE]);
        BlogCategory::firstOrCreate(['title' => "Distributed ledger technology",], ['sub' => STATUS_ACTIVE, 'main_id' => 3, 'status' => STATUS_ACTIVE]);
        BlogCategory::firstOrCreate(['title' => "Smart contracts",], ['sub' => STATUS_ACTIVE, 'main_id' => 3, 'status' => STATUS_ACTIVE]);
    }
}
