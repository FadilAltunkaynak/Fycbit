<?php

namespace Modules\KnowledgeBase\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\KnowledgeBase\Entities\KnowledgebaseFontawesome;

class KnowledgebaseFontawesomeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $date =  Carbon::now();
        $data = [
            ['title'=>'house','class_name'=>'fa fa-home', 'unicode'=>'f015', 'created_at' => $date, 'updated_at' => $date],
            ['title'=>'fa-address-book','class_name'=>'fa fa-address-book', 'unicode'=>'f2b9', 'created_at' => $date, 'updated_at' => $date],
            ['title'=>'fa-address-book-o','class_name'=>'fa fa-address-book-o', 'unicode'=>'f2ba', 'created_at' => $date, 'updated_at' => $date],
            ['title'=>'fa-address-card','class_name'=>'fa fa-address-card', 'unicode'=>'f2bb', 'created_at' => $date, 'updated_at' => $date],
            ['title'=>'fa-user-circle','class_name'=>'fa fa-user-circle', 'unicode'=>'f2bd', 'created_at' => $date, 'updated_at' => $date],
            ['title'=>'fa-handshake-o','class_name'=>'fa fa-handshake-o', 'unicode'=>'f2b5', 'created_at' => $date, 'updated_at' => $date],
            ['title'=>'fa-bell','class_name'=>'fa fa-bell', 'unicode'=>'f0f3', 'created_at' => $date, 'updated_at' => $date],
            ['title'=>'fa-area-chart','class_name'=>'fa fa-area-chart', 'unicode'=>'f1fe', 'created_at' => $date, 'updated_at' => $date],
        ];

        KnowledgebaseFontawesome::truncate()->insert($data);
    }
}
