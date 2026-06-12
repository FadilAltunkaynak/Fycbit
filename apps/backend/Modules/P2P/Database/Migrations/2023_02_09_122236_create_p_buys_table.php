<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePBuysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('p_buys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uid', 180)->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('coin_id');
            $table->string('coin_type');
            $table->unsignedBigInteger('wallet_id');
            $table->decimal('available',29,18)->default(0);
            $table->decimal('sold',29,18)->default(0);
            $table->text('country');
            $table->string('currency');
            $table->string('ip');
            $table->integer('payment_times')->default(0);
            $table->text('payment_method');
            $table->decimal('amount',29,18)->default(0);
            $table->decimal('price_rate',29,18)->default(0);
            $table->decimal('rate_percentage',29,18)->default(0);
            $table->decimal('price',29,18)->default(0);
            $table->tinyInteger('price_type')->default(1);
            $table->decimal('minimum_trade_size',19,2)->default(0);
            $table->decimal('maximum_trade_size',19,2)->default(0);
            $table->longText('terms')->nullable();
            $table->longText('auto_reply')->nullable();
            $table->unsignedBigInteger('register_days')->default(0);
            $table->decimal('coin_holding',29,18)->default(0);
            $table->tinyInteger('kyc_completed')->default(0);
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
        Schema::dropIfExists('p_buys');
    }
}
