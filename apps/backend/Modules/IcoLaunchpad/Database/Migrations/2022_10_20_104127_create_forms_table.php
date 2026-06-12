<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->longText('title')->nullable();
            $table->tinyInteger('type')->nullable();
            $table->tinyInteger('required')->nullable();
            $table->tinyInteger('is_option')->nullable();
            $table->longText('optionList')->nullable();
            $table->tinyInteger('is_file')->nullable();
            $table->string('file_type')->nullable();
            $table->string('file_link')->nullable();
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
        Schema::dropIfExists('forms');
    }
}
