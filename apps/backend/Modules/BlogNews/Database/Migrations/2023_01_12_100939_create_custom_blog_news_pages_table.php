<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomBlogNewsPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_blog_news_pages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('type')->default(1);
            $table->string('title');
            $table->string('slug');
            $table->longText('body');
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom_blog_news_pages');
    }
}
