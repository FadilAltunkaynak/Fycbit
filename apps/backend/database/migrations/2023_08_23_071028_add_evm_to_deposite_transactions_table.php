<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEvmToDepositeTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('deposite_transactions', function (Blueprint $table) {
            $table->unsignedInteger("network_id")->nullable();
            $table->unsignedInteger("coin_id")->nullable();;
            $table->string("block_number")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('deposite_transactions', function (Blueprint $table) {
            $table->dropColumn("network_id");
            $table->dropColumn("coin_id");
            $table->dropColumn("block_number");
        });
    }
}
