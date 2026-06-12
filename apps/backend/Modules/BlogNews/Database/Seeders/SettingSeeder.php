<?php
namespace Modules\BlogNews\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\BlogNews\Entities\BlogNewsSetting;

class SettingSeeder extends Seeder {

    public function run()
    {   
        //Blog Comment
        BlogNewsSetting::firstOrCreate(['slug'=>'blog_comment_enable'],['value'=>'1']);
        BlogNewsSetting::firstOrCreate(['slug'=>'blog_auto_comment_approval'],['value'=>'1']);

        //News Comment
        BlogNewsSetting::firstOrCreate(['slug'=>'news_comment_enable'],['value'=>'1']);
        BlogNewsSetting::firstOrCreate(['slug'=>'news_auto_comment_approval'],['value'=>'1']);

        //Blog feature
        BlogNewsSetting::firstOrCreate(['slug'=>'blog_feature_enable'],['value'=>'1']);
        BlogNewsSetting::firstOrCreate(['slug'=>'blog_feature_heading'],['value'=>"This is Feature Heading"]);
        BlogNewsSetting::firstOrCreate(['slug'=>'blog_feature_description'],['value'=>"This is Feature description"]);

        //Blog Search
        BlogNewsSetting::firstOrCreate(['slug'=>'blog_search_enable'],['value'=>'1']);


    }

}