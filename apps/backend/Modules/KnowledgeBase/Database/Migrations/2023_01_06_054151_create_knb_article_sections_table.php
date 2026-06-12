<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKnbArticleSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('knb_article_sections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('unique_code')->nullable();
            $table->unsignedBigInteger('article_id')->nullable();
            $table->text('title')->nullable();
            $table->string('icon_class')->nullable();
            $table->longText('description')->nullable();
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('knb_article_sections');
    }
}
