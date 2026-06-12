<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePCoinSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('p_coin_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('coin_type');
            $table->string('minimum_price')->nullable();
            $table->string('maximum_price')->nullable();
            $table->string('buy_fees')->default(0);
            $table->string('sell_fees')->default(0);
            $table->tinyInteger('trade_status')->default(1);
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
        Schema::dropIfExists('p_coin_settings');
    }
}
