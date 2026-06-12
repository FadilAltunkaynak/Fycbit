<?php

namespace Modules\DemoTrade\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\DemoTrade\Database\Seeders\SeedDemoCoinsTableSeeder;

class DemoTradeDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(SeedDemoCoinsTableSeeder::class);
    }
}
