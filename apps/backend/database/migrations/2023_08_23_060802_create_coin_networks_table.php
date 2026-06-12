<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoinNetworksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_networks', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->unsignedInteger('network_id');
            $table->unsignedInteger('currency_id');
            $table->tinyInteger('type');
            $table->string('contract_address');
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
        Schema::dropIfExists('coin_networks');
    }
}
