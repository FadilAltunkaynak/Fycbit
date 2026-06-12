<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFaucetHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection("DemoTradeMysql")->create('faucet_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('coin_id');
            $table->unsignedInteger('wallet_id');
            $table->string('coin_type');
            $table->decimal('amount', 29, 18);
            $table->dateTime('next_time');
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
        Schema::connection("DemoTradeMysql")->dropIfExists('faucet_histories');
    }
}
