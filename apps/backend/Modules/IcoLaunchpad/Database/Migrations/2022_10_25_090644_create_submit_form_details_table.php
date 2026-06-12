<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubmitFormDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('submit_form_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('unique_id')->nullable();
            $table->longText('question')->nullable();
            $table->longText('answer')->nullable();
            $table->tinyInteger('is_input')->default(false);
            $table->tinyInteger('is_option')->default(false);
            $table->tinyInteger('is_file')->default(false);
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
        Schema::dropIfExists('submit_form_details');
    }
}
