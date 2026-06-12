<?php

namespace Modules\KnowledgeBase\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\KnowledgeBase\Entities\KnowledgebaseSupportSetting;

class KnowledgebaseSupportSettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        KnowledgebaseSupportSetting::firstOrCreate(['slug'=>'knowledgebase_url_type'],['value'=>KNOWLEDGEBASE_URL_TYPE_FRONTEND]);
        KnowledgebaseSupportSetting::firstOrCreate(['slug'=>'knowledgebase_backend_url_link'],['value'=>route('knowledgebase_user_index')]);
        KnowledgebaseSupportSetting::firstOrCreate(['slug'=>'logo'],['value'=>'']);
        KnowledgebaseSupportSetting::firstOrCreate(['slug'=>'cover_image'],['value'=>'']);
        KnowledgebaseSupportSetting::firstOrCreate(['slug'=>'knowledgebase'],['value'=>'KnowledgeBase']);
        KnowledgebaseSupportSetting::firstOrCreate(['slug'=>'support'],['value'=>'Support']);
        KnowledgebaseSupportSetting::firstOrCreate(['slug'=>'knowledgebase_page_cover_first_title'],['value'=>'How can we help you ?']);
        KnowledgebaseSupportSetting::firstOrCreate(['slug'=>'knowledgebase_page_cover_second_title'],['value'=>'Ask Questions Browse Articles Find Answers']);
        KnowledgebaseSupportSetting::firstOrCreate(['slug'=>'support_page_cover_first_title'],['value'=>'How can we help you ?']);
        KnowledgebaseSupportSetting::firstOrCreate(['slug'=>'support_page_cover_second_title'],['value'=>'If you have any problem, you can create a ticket to get support']);
        KnowledgebaseSupportSetting::firstOrCreate(['slug'=>'create_ticket_button_text'],['value'=>'Create Ticket']);
        // $this->call("OthersTableSeeder");
    }
}
