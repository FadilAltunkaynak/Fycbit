<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePCurrencySettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('p_currency_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('currency_code');
            $table->string('minimum_price')->nullable();
            $table->string('maximum_price')->nullable();
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
        Schema::dropIfExists('p_currency_settings');
    }
}
