<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFutureCoinPairsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('future_coin_pairs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uid')->unique();
            $table->string('code');
            $table->tinyInteger('collateral_type')->default(1);
            $table->tinyInteger('margin_mode')->default(1);
            $table->unsignedBigInteger('base_coin_id');
            $table->unsignedBigInteger('trade_coin_id');
            $table->string('base_coin_code');
            $table->string('trade_coin_code');
            $table->tinyInteger('base_decimal')->default(4);
            $table->tinyInteger('trade_decimal')->default(4);
            $table->unsignedDecimal('maker_fees_percent', 8,5)->default(0);
            $table->unsignedDecimal('taker_fees_percent', 8,5)->default(0);
            $table->unsignedDecimal('min_amount', 29,18)->default(0);
            $table->unsignedDecimal('max_amount', 29,18)->default(0);
            $table->unsignedDecimal('min_stop_limit_percent', 8,5)->default(0);
            $table->unsignedDecimal('max_stop_limit_percent', 8,5)->default(0);
            $table->unsignedDecimal('floor_ratio', 8,5)->default(0);
            $table->unsignedDecimal('cap_ratio', 8,5)->default(0);
            $table->unsignedInteger('leverage')->default(1);
            $table->unsignedInteger('max_leverage')->default(125);
            $table->unsignedInteger('max_open_orders')->default(125);
            $table->unsignedDecimal('funding_rate', 8,5)->default(0);
            $table->unsignedDecimal('slippage_percent', 8,5)->default(0);
            $table->dateTime('funding_next_time');
            $table->tinyInteger('status')->default(0);
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
        Schema::dropIfExists('future_coin_pairs');
    }
}
