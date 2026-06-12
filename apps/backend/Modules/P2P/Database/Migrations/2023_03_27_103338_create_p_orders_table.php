<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('p_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uid', 180)->unique();
            $table->string('order_id',180)->nullable()->unique();
            $table->unsignedBigInteger('buyer_id');
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('buyer_wallet_id')->nullable();
            $table->unsignedBigInteger('seller_wallet_id')->nullable();
            $table->unsignedBigInteger('sell_id')->nullable();
            $table->unsignedBigInteger('buy_id')->nullable();
            $table->string('coin_type');
            $table->string('currency');
            $table->decimal('rate',29,18)->default(0);
            $table->decimal('amount',29,18)->default(0)->unsigned();
            $table->decimal('price',29,18)->default(0);
            $table->decimal('seller_fees',29,18)->default(0);
            $table->decimal('buyer_fees',29,18)->default(0);
            $table->decimal('seller_fees_percentage',29,18)->default(0);
            $table->decimal('buyer_fees_percentage',29,18)->default(0);
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('is_reported')->default(0);
            $table->tinyInteger('payment_status')->default(0);
            $table->tinyInteger('is_queue')->default(0);
            $table->string('payment_id');
            $table->string('payment_sleep')->nullable();
            $table->string('transaction_id')->nullable();
            $table->integer('payment_time')->default(0);
            $table->dateTime('payment_expired_time')->nullable();
            $table->text('admin_note')->nullable();
            $table->bigInteger('who_opened')->nullable();
            $table->bigInteger('who_cancelled')->nullable();
            $table->tinyInteger('is_success')->default(0);

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
        Schema::dropIfExists('p_orders');
    }
}
