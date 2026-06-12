<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePGiftCardOrderDisputesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('p_gift_card_order_disputes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uid', 180)->unique();
            $table->unsignedBigInteger('gift_card_order_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('reported_user');
            $table->text('reason_heading')->nullable();
            $table->longText('details')->nullable();
            $table->string('image')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->bigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('assigned_admin')->nullable();
            $table->dateTime('expired_at')->nullable();
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
        Schema::dropIfExists('p_gift_card_order_disputes');
    }
}
