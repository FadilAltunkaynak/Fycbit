<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdminPaymentToBuySellTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('p_buys', function (Blueprint $table) {
            $table->text('admin_payment_method')->nullable();
        });
        Schema::table('p_sells', function (Blueprint $table) {
            $table->text('admin_payment_method')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('p_buys', function (Blueprint $table) {
            $table->dropColumn('admin_payment_method');
        });
        Schema::table('p_sells', function (Blueprint $table) {
            $table->dropColumn('admin_payment_method');
        });
    }
}
