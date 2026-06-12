<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFuturePositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('future_positions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uid')->unique();
            $table->unsignedBigInteger('future_coin_pair_id');
            $table->string('future_coin_pair_uid');
            $table->unsignedBigInteger('base_coin_id');
            $table->unsignedBigInteger('trade_coin_id');
            $table->unsignedBigInteger('future_wallet_id');
            $table->unsignedBigInteger('user_id');
            $table->tinyInteger('order_type');
            $table->unsignedDecimal('price', 29,18)->default(0);
            $table->Decimal('amount', 29,18)->default(0);
            $table->unsignedDecimal('tp_price', 29,18)->default(0);
            $table->unsignedDecimal('sl_price', 29,18)->default(0);
            $table->unsignedDecimal('margin_balance', 29,18)->default(0);
            $table->tinyInteger('margin_mode')->default(1);
            $table->tinyInteger('leverage')->default(0);
            $table->tinyInteger('status')->default(0);
            $table->timestamps();

            $table->unique([
                'future_coin_pair_id',
                'user_id',
            ], 'future_positions_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('future_positions');
    }
}
