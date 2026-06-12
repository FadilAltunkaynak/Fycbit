<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFutureBotSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('future_bot_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uid')->unique()->index();
            $table->unsignedBigInteger('future_coin_pair_id')->unique();
            $table->unsignedDecimal('amount_low', 29, 18)->default(0);
            $table->unsignedDecimal('amount_high', 29, 18)->default(0);
            $table->unsignedDecimal('price_low', 29, 18)->default(0);
            $table->unsignedDecimal('price_high', 29, 18)->default(0);
            $table->unsignedInteger('order_interval')->default(1);
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('future_bot_settings');
    }
}
