<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBankDetailsToTokenWithdrawTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('token_withdraw_transactions', function (Blueprint $table) {
            $table->text("payment_details")->nullable();
            $table->string("payment_sleep", 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('token_withdraw_transactions', function (Blueprint $table) {

        });
    }
}
