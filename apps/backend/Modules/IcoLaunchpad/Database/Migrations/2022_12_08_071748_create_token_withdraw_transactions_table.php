<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTokenWithdrawTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('token_withdraw_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->decimal('request_amount',19, 8)->nullable();
            $table->string('request_currency')->nullable();
            $table->decimal('convert_amount',19, 8)->nullable();
            $table->string('convert_currency')->nullable();
            $table->tinyInteger('tran_type')->nullable();
            $table->tinyInteger('approved_status')->default(0);
            $table->unsignedBigInteger('approved_by_id')->nullable();
            $table->decimal('fee',19, 8)->nullable();
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
        Schema::dropIfExists('token_withdraw_transactions');
    }
}
