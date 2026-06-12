<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePGiftCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('p_gift_cards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uid')->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('gift_card_id');
            $table->tinyInteger('payment_currency_type');
            $table->string('currency_type');
            $table->decimal('price',19,8);
            $table->decimal('amount',19,8)->nullable();
            $table->text('terms_condition')->nullable();
            $table->text('country')->nullable();
            $table->integer('time_limit')->nullable();
            $table->text('auto_reply')->nullable();
            $table->integer('user_registered_before')->nullable();
            $table->text('payment_method')->nullable();
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('p_gift_cards');
    }
}
