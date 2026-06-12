<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTokenBuyHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('token_buy_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('coin_id');
            $table->bigInteger('phase_id');
            $table->bigInteger('token_id');
            $table->bigInteger('user_id');
            $table->decimal('amount',$precision=19, $scale=8)->nullable();
            $table->tinyInteger('payment_method')->nullable();
            $table->bigInteger('wallet_id')->nullable();
            $table->bigInteger('payer_wallet')->nullable();
            $table->bigInteger('trx_id')->nullable();
            $table->integer('bank_id')->nullable();
            $table->decimal('payer_coin',19, 8)->nullable();
            $table->string('bank_ref',255)->nullable();
            $table->string('bank_slip',255)->nullable();
            $table->string('buy_currency')->nullable();
            $table->decimal('pay_amount',19, 8)->nullable();
            $table->string('pay_currency',10)->nullable();
            $table->decimal('buy_price',$precision=19, $scale=8)->nullable();
            $table->string('blockchain_tx')->nullable();
            $table->decimal('used_gas',29,18)->nullable();
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
        Schema::dropIfExists('token_buy_histories');
    }
}
