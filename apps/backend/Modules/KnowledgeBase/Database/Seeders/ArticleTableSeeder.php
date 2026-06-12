<?php

namespace Modules\KnowledgeBase\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\KnowledgeBase\Entities\KnbArticle;
use Modules\KnowledgeBase\Entities\KnbArticleSection;
use Modules\KnowledgeBase\Entities\KnbCategory;
use Modules\KnowledgeBase\Entities\KnbSubCategory;

class ArticleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $date =  Carbon::now();
        $category = [
            ['unique_code' => uniqid() . date('') . time(), 'name' => 'Tradexpro Exchange', 'icon_class' => 'fa fa-home', 'description' => '', 'status' => 1, 'created_at' => $date, 'updated_at' => $date],
            ['unique_code' => uniqid() . date('') . time(), 'name' => 'ICO Launchpad', 'icon_class' => 'fa fa-home', 'description' => '', 'status' => 1, 'created_at' => $date, 'updated_at' => $date]
        ];
        KnbCategory::truncate()->insert($category);
        $subcategory = [
            ['category_id'=>1,'unique_code' => uniqid() . date('') . time(), 'name' => 'Technical', 'icon_class' => 'fa fa-bell', 'status' => 1, 'created_at' => $date, 'updated_at' => $date],
            ['category_id'=>1,'unique_code' => uniqid() . date('') . time(), 'name' => 'Installation', 'icon_class' => 'fa fa-bell', 'status' => 1, 'created_at' => $date, 'updated_at' => $date],
            ['category_id'=>1,'unique_code' => uniqid() . date('') . time(), 'name' => 'Face Issue', 'icon_class' => 'fa fa-bell', 'status' => 1, 'created_at' => $date, 'updated_at' => $date],
            ['category_id'=>2,'unique_code' => uniqid() . date('') . time(), 'name' => 'Technical', 'icon_class' => 'fa fa-bell', 'status' => 1, 'created_at' => $date, 'updated_at' => $date],
            ['category_id'=>2,'unique_code' => uniqid() . date('') . time(), 'name' => 'Installation', 'icon_class' => 'fa fa-bell', 'status' => 1, 'created_at' => $date, 'updated_at' => $date],
            ['category_id'=>2,'unique_code' => uniqid() . date('') . time(), 'name' => 'Face Issue', 'icon_class' => 'fa fa-bell',  'status' => 1, 'created_at' => $date, 'updated_at' => $date]
        ];
        KnbSubCategory::truncate()->insert($subcategory);
        $description = "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.";

        $article = [
            ['unique_code' => uniqid() . date('') . time(), 'category_id' => 1, 'sub_category_id' => 1, 'title' => 'How tradexpro exchange perform ?', 'icon_class' => 'fa fa-address-card', 'description' => $description, 'status' => 1, 'created_at' => $date, 'updated_at' => $date],
            ['unique_code' => uniqid() . date('') . time(), 'category_id' => 1, 'sub_category_id' => 2, 'title' => 'What is the main procedure of trading ?', 'icon_class' => 'fa fa-address-card', 'description' => $description, 'status' => 1, 'created_at' => $date, 'updated_at' => $date],
            ['unique_code' => uniqid() . date('') . time(), 'category_id' => 1, 'sub_category_id' => 3, 'title' => 'How Ico Launchpad work ?', 'icon_class' => 'fa fa-address-card', 'description' => $description, 'status' => 1, 'created_at' => $date, 'updated_at' => $date],
            ['unique_code' => uniqid() . date('') . time(), 'category_id' => 2, 'sub_category_id' => 4, 'title' => 'What is the main benefit of trading', 'icon_class' => 'fa fa-address-card', 'description' => $description, 'status' => 1, 'created_at' => $date, 'updated_at' => $date],
            ['unique_code' => uniqid() . date('') . time(), 'category_id' => 2, 'sub_category_id' => 5, 'title' => 'How you earn from exchange ?', 'icon_class' => 'fa fa-address-card', 'description' => $description, 'status' => 1, 'created_at' => $date, 'updated_at' => $date],
            ['unique_code' => uniqid() . date('') . time(), 'category_id' => 2, 'sub_category_id' => 6, 'title' => 'What is basic concept of stop limit buy sell?', 'icon_class' => 'fa fa-address-card', 'description' => $description, 'status' => 1, 'created_at' => $date, 'updated_at' => $date]
        ];
        KnbArticle::truncate()->insert($article);
        KnbArticleSection::truncate();
        // $this->call("OthersTableSeeder");
    }
}
