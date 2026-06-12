<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlogPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('thumbnail')->nullable();
            $table->unsignedTinyInteger('category');
            $table->unsignedTinyInteger('sub_category')->nullable();
            $table->longText('body');
            $table->string('keywords')->nullable();
            $table->string('description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->dateTime('publish_at')->nullable();
            $table->tinyInteger('publish')->default(0);
            $table->tinyInteger('comment_allow')->default(0);
            $table->unsignedBigInteger('views')->default(0);
            $table->tinyInteger('is_fetured')->default(0);
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
        Schema::dropIfExists('blog_posts');
    }
}
