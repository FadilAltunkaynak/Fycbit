<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFutureWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::hasTable('future_wallets') && !Schema::hasColumn('future_wallets','margin_balance') && Schema::drop('future_wallets');
        Schema::create('future_wallets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("name");
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("coin_id");
            $table->unsignedDecimal("balance", 29, 18)->default(0);
            $table->unsignedDecimal("available_balance", 29, 18)->default(0);
            $table->unsignedDecimal("in_order_balance", 29, 18)->default(0);
            $table->unsignedDecimal("margin_balance", 29, 18)->default(0);
            $table->tinyInteger("status")->default(1);
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
        Schema::dropIfExists('future_wallets');
    }
}
