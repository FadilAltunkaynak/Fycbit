<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIcoPhasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ico_phase_infos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('ico_token_id')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->decimal('coin_price',$precision=19, $scale=8)->default(0);
            $table->string('coin_currency')->nullable();
            $table->decimal('total_token_supply',$precision=29, $scale=18)->default(0);
            $table->decimal('available_token_supply',$precision=29, $scale=18)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('phase_title')->nullable();
            $table->longText('description')->nullable();
            $table->string('image')->nullable();
            $table->string('video_link')->nullable();
            $table->longText('social_link')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('is_featured')->default(0);
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
        Schema::dropIfExists('ico_phase_infos');
    }
}
