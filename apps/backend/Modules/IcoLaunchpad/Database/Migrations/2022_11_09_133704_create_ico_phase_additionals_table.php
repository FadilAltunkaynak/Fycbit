<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIcoPhaseAdditionalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ico_phase_additionals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('ico_phase_id')->nullable();
            $table->text('title')->nullabe();
            $table->longText('value')->nullabe();
            $table->string('file')->nullable();
            $table->tinyInteger('is_updated')->default(0);
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
        Schema::dropIfExists('ico_phase_additionals');
    }
}
