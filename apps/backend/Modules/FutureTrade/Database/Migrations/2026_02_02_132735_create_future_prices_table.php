<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFuturePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('future_prices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('future_coin_pair_id');
            $table->uuid('future_coin_pair_uid');
            $table->unsignedDecimal('market_price', 29, 18)->default(0);
            $table->unsignedDecimal('mark_price', 29, 18)->default(0);
            $table->unsignedDecimal('index_price', 29, 18)->default(0);
            $table->decimal('change_24h', 8, 5)->default(0);
            $table->unsignedDecimal('high_24h', 29, 18)->default(0);
            $table->unsignedDecimal('low_24h', 29, 18)->default(0);
            $table->unsignedDecimal('volume_24h_btc', 29, 18)->default(0);
            $table->unsignedDecimal('volume_24h_usdt', 29, 18)->default(0);
            $table->index('future_coin_pair_id');
            $table->timestamps();

            $table->foreign('future_coin_pair_id')
                ->references('id')
                ->on('future_coin_pairs')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('future_prices');
    }
}
