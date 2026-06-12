<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFutureTradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('future_trades', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uid')->unique();

            $table->unsignedBigInteger('future_coin_pair_id');
            $table->unsignedBigInteger('base_coin_id');
            $table->unsignedBigInteger('trade_coin_id');
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('buyer_id');
            $table->unsignedBigInteger('maker_id');
            $table->unsignedBigInteger('taker_id');
            $table->unsignedBigInteger('buy_id');
            $table->unsignedBigInteger('sell_id');

            $table->tinyInteger('order_type');

            $table->unsignedDecimal('price', 29,18)->default(0);
            $table->unsignedDecimal('amount', 29,18)->default(0);
            $table->unsignedDecimal('total_price', 29,18)->default(0);
            $table->unsignedDecimal('last_price', 29,18)->default(0);
            $table->unsignedDecimal('taker_fees', 12,6)->default(0);
            $table->unsignedDecimal('maker_fees', 12,6)->default(0);

            $table->decimal('buyer_realized_profit', 29, 18)->default(0);
            $table->decimal('seller_realized_profit', 29, 18)->default(0);
            $table->tinyInteger('status')->default(0);
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
        Schema::dropIfExists('future_trades');
    }
}
