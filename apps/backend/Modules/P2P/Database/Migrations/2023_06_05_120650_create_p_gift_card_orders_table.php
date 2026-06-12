<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePGiftCardOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('p_gift_card_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uid')->unique();
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('buyer_id');
            $table->unsignedBigInteger('p_gift_card_id');
            $table->tinyInteger('payment_currency_type');
            $table->string('currency_type');
            $table->decimal('price',19,8);
            $table->decimal('amount',19,8);
            $table->integer('payment_time')->nullable();
            $table->dateTime('payment_expired_time')->nullable();
            $table->string('payment_method_id')->nullable();
            $table->string('payment_sleep')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('is_reported')->default(0);
            $table->unsignedBigInteger('reported_user')->nullable();
            $table->tinyInteger('payment_status')->default(0);
            $table->tinyInteger('is_queue')->default(0);
            $table->string('transaction_id')->nullable();
            $table->text('admin_note')->nullable();
            $table->unsignedBigInteger('who_cancelled')->nullable();
            $table->tinyInteger('is_success')->default(0);
            $table->tinyInteger('buyer_feedback_type')->nullable();
            $table->string('buyer_feedback')->nullable();
            $table->tinyInteger('seller_feedback_type')->nullable();
            $table->string('seller_feedback')->nullable();
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
        Schema::dropIfExists('p_gift_card_orders');
    }
}
