<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoinPairsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('DemoTradeMysql')->create('coin_pairs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('parent_coin_id');
            $table->integer('child_coin_id');
            $table->decimal('value', 19, 8)->default(0);
            $table->decimal('volume', 19, 8)->default(0);
            $table->decimal('change', 19, 8)->default(0);
            $table->decimal('high', 19, 8)->default(0);
            $table->decimal('low', 19, 8)->default(0);
            $table->decimal('initial_price', 19, 8)->default(0);
            $table->decimal('price', 19, 8)->default(0);
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('is_chart_updated')->default(0);
            $table->tinyInteger('bot_trading')->default(1);
            $table->tinyInteger('bot_trading_buy')->default(1);
            $table->tinyInteger('bot_trading_sell')->default(1);
            $table->tinyInteger('is_token')->default(0);
            $table->tinyInteger('bot_possible')->default(1);
            $table->unique(['parent_coin_id', 'child_coin_id']);
            $table->softDeletes();
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
        Schema::connection('DemoTradeMysql')->dropIfExists('coin_pairs');
    }
}
