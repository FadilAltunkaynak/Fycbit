<?php

namespace Modules\BlogNews\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\BlogNews\Database\Seeders\SettingSeeder;
use Modules\BlogNews\Database\Seeders\BlogPostTableSeeder;
use Modules\BlogNews\Database\Seeders\NewsPostTableSeeder;
use Modules\BlogNews\Database\Seeders\BlogCategoryTableSeeder;
use Modules\BlogNews\Database\Seeders\NewsCategoryTableSeeder;

class BlogNewsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $this->call(SettingSeeder::class);
        $this->call(NewsCategoryTableSeeder::class);
        $this->call(BlogCategoryTableSeeder::class);
        $this->call(BlogPostTableSeeder::class);
        $this->call(NewsPostTableSeeder::class);
    }
}
