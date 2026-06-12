<?php

namespace Modules\BlogNews\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\BlogNews\Entities\NewsCategory;

class NewsCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        NewsCategory::firstOrCreate(['title' => "Market News",], ['sub' => STATUS_INACTIVE, 'status' => STATUS_ACTIVE]);
        NewsCategory::firstOrCreate(['title' => "Regulatory News",], ['sub' => STATUS_INACTIVE, 'status' => STATUS_ACTIVE]);
        NewsCategory::firstOrCreate(['title' => "Business and Adoption News",], ['sub' => STATUS_INACTIVE, 'status' => STATUS_ACTIVE]);
        NewsCategory::firstOrCreate(['title' => "Mining News",], ['sub' => STATUS_INACTIVE, 'status' => STATUS_ACTIVE]);
    }
}
