<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFeedbackToPOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('p_orders', function (Blueprint $table) {
            $table->boolean('buyer_feedback_type')->nullable();
            $table->string('buyer_feedback')->nullable();
            $table->boolean('seller_feedback_type')->nullable();
            $table->string('seller_feedback')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('p_orders', function (Blueprint $table) {
            $table->dropColumn('buyer_feedback_type')->nullable();
            $table->dropColumn('buyer_feedback')->nullable();
            $table->dropColumn('seller_feedback_type')->nullable();
            $table->dropColumn('seller_feedback')->nullable();
        });
    }
}
