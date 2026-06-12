<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFutureSellsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('future_sells', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uid')->unique();
            $table->unsignedBigInteger('future_coin_pair_id');
            $table->unsignedBigInteger('base_coin_id');
            $table->unsignedBigInteger('trade_coin_id');
            $table->unsignedBigInteger('user_id');
            $table->tinyInteger('margin_mode')->default(1);
            $table->tinyInteger('order_method')->default(1);
            $table->tinyInteger('is_reduce')->default(0);
            $table->unsignedDecimal('price', 29,18)->default(0);
            $table->unsignedDecimal('amount', 29,18)->default(0);
            $table->unsignedDecimal('total_price', 29,18)->default(0);
            $table->unsignedDecimal('processed_amount', 29,18)->default(0);
            $table->unsignedDecimal('pending_amount', 29,18)->default(0);
            $table->unsignedDecimal('stop_price', 29,18)->default(0);
            $table->unsignedDecimal('market_price', 29,18)->default(0);
            $table->unsignedDecimal('mark_price', 29,18)->default(0);
            $table->unsignedDecimal('index_price', 29,18)->default(0);
            $table->unsignedDecimal('tp_price', 29,18)->default(0);
            $table->unsignedDecimal('sl_price', 29,18)->default(0);
            $table->tinyInteger('tpsl_type')->default(0);
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('order_type')->default(2);
            $table->tinyInteger('is_bot')->default(0);
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
        Schema::dropIfExists('future_sells');
    }
}
