<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFuturePositionHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('future_position_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('future_position_id');
            $table->unsignedDecimal('available_balance', 29,18)->default(0);
            $table->unsignedDecimal('margin_balance', 29,18)->default(0);
            $table->unsignedDecimal('mark_price', 29,18)->default(0);
            $table->unsignedDecimal('price', 29,18)->default(0);
            $table->decimal('amount', 29,18)->default(0);
            $table->tinyInteger('leverage')->default(0);
            $table->tinyInteger('margin_mode')->default(1);
            $table->unsignedDecimal('liquidation_price', 29,18)->default(0);
            $table->unsignedDecimal('pnl', 29,18)->default(0);
            $table->unsignedDecimal('maintenance_margin', 29,18)->default(0);
            $table->unsignedDecimal('margin_ratio', 29,18)->default(0);
            $table->string('liq_for')->nullable();
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
        Schema::dropIfExists('future_position_histories');
    }
}
