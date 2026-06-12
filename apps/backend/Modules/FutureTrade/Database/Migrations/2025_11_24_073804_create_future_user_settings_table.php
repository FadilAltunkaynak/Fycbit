<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFutureUserSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('future_user_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uid')->unique()->index();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('coin_pair_id');
            $table->tinyInteger('leverage')->default(1);
            $table->tinyInteger('margin_mode')->default(1);
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
        Schema::dropIfExists('future_user_settings');
    }
}
