<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoinPaymentWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_payment_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coin_id');
            $table->string('coin_type');
            $table->string('code')->nullable();
            $table->string('wallet_id');
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
        Schema::dropIfExists('coin_payment_wallets');
    }
}
