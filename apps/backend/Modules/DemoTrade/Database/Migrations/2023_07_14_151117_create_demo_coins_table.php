<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDemoCoinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection("DemoTradeMysql")->create('demo_coins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger("coin_id");
            $table->string("coin_type");
            $table->boolean("trade_status");
            $table->decimal("faucet_amount", 29,18)->default(0);
            $table->integer("faucet_time")->default(72);
            $table->decimal("faucet_min_balance", 29,18)->default(1000);
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
        Schema::connection("DemoTradeMysql")->dropIfExists('demo_coins');
    }
}
